#include <TinyGPS++.h>
#include <LoRa.h>
#include <Wire.h>
#include <WiFi.h>
#include <WiFiManager.h>
#include <HTTPClient.h>

// Pin definitions
const int trigPin1 = 5;
const int echoPin1 = 4;
const int trigPin2 = 14;
const int echoPin2 = 12;
const int gpsTxPin = 17;
const int gpsRxPin = 16;
const int buzzerPin = 18;
const int batteryPin = 36;

// LoRa pins
#define LORA_SS 15
#define LORA_RST 21
#define LORA_DIO0 26
#define LORA_SCK 22
#define LORA_MISO 19
#define LORA_MOSI 23

#define DEVICE_1_ADDRESS 0x01
#define DEVICE_2_ADDRESS 0x03

TinyGPSPlus gps;
WiFiManager wifiManager; // Create an instance of WiFiManager
int Slot = 0;
unsigned long previousMillis = 0;
const long interval = 1000; // interval at which to send data (milliseconds)
bool leftDetected = false;
bool rightDetected = false;
float bearing = 0; // Bearing variable to store GPS heading in degrees

unsigned long previousGPSMillis = 0; // Variable to store the last time GPS was checked
const long gpsInterval = 1000; // 1 second in milliseconds

// Declare variables to store previous GPS data
float previousLat = 0.0;
float previousLng = 0.0;
float previousSpeed = 0.0;
float previousBearing = 0.0;

unsigned long previousHTTPMillis = 0;
const long httpInterval = 30000; // 30 seconds in milliseconds

float lastValidBearing = 0.0;

void setup() {
    Serial.begin(9600);
    Serial2.begin(9600, SERIAL_8N1, gpsRxPin, gpsTxPin);

    pinMode(trigPin1, OUTPUT);
    pinMode(echoPin1, INPUT);
    pinMode(trigPin2, OUTPUT);
    pinMode(echoPin2, INPUT);
    pinMode(buzzerPin, OUTPUT);
    pinMode(batteryPin, INPUT);

    // Initialize WiFi
    wifiManager.autoConnect("AutoConnectAP");

    // Initialize LoRa
    SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_SS);
    LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
    if (!LoRa.begin(433E6)) {
        Serial.println("Starting LoRa failed!");
        while (1);
    }
    Serial.println("LoRa Initializing OK!");
}

void loop() {
    int packetSize = LoRa.parsePacket();
    if (packetSize) {
        int sender = LoRa.read();    // Read the sender address
        int receiver = LoRa.read();  // Read the receiver address
        
        // Check if message is for this device AND from Device 2
        if (receiver == DEVICE_1_ADDRESS && sender == DEVICE_2_ADDRESS) {
            String message = "";
            while (LoRa.available()) {
                message += (char)LoRa.read();
            }
            Serial.println("Received message: " + message);

            // Process button commands from Device 2
            if (message == "BUTTON1_PRESSED") {
                if (Slot > 0) {
                    Slot--;
                    Serial.println("Button 1 pressed: Decrement Slot");
                    triggerBuzzer();
                    if (Slot == 0) {
                        // Update database and send LoRa message when Slot becomes 0
                        updateDatabase(DEVICE_1_ADDRESS, String(bearing));
                        sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=0");
                        Serial.println("Slot is now 0");
                    }
                } else {
                    Slot = 0;
                    Serial.println("Cannot decrement Slot, it's already 0");
                }
            } else if (message == "BUTTON2_PRESSED") {
                Slot++;
                Serial.println("Button 2 pressed: Increment Slot");
                triggerBuzzer();
            }
        }
    }

    int rawValue = analogRead(batteryPin);
    float voltage = (rawValue / 4095.0) * 3.3 * 2;
    int batteryPercent = calculateBatteryPercentage(voltage);

    delay(50);

    int distance1 = getDistance(trigPin1, echoPin1);
    int distance2 = getDistance(trigPin2, echoPin2);

    unsigned long currentMillis = millis();
    
    // Check GPS data every 20 seconds
    if (currentMillis - previousGPSMillis >= gpsInterval) {
        previousGPSMillis = currentMillis;
        
        while (Serial2.available() > 0) {
            if (gps.encode(Serial2.read())) {
                bool dataChanged = false;
                
                if (gps.location.isValid()) {
                    float currentLat = gps.location.lat();
                    float currentLng = gps.location.lng();
                    if (currentLat != previousLat || currentLng != previousLng) {
                        Serial.print("Latitude: ");
                        Serial.println(currentLat, 6);
                        Serial.print("Longitude: ");
                        Serial.println(currentLng, 6);
                        previousLat = currentLat;
                        previousLng = currentLng;
                        dataChanged = true;
                    }
                }
                if (gps.speed.isValid()) {
                    float currentSpeed = gps.speed.kmph();
                    if (currentSpeed != previousSpeed) {
                        Serial.print("Speed: ");
                        Serial.print(currentSpeed);
                        Serial.println(" kmph");
                        previousSpeed = currentSpeed;
                        dataChanged = true;
                    }
                }
                if (gps.course.isValid()) {
                    float currentBearing = gps.course.deg();
                    if (currentBearing != previousBearing) {
                        Serial.print("Bearing: ");
                        Serial.println(currentBearing);
                        previousBearing = currentBearing;
                        lastValidBearing = currentBearing; // Update last valid bearing
                        dataChanged = true;
                    }
                }

                // Send HTTP request if data has changed
                if (dataChanged) {
                    updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
                }
            }
        }
    }

    // Send HTTP request at regular intervals
    if (currentMillis - previousHTTPMillis >= httpInterval) {
        previousHTTPMillis = currentMillis;
        updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
    }

    // Check ultrasonic sensor distances and update Slot
    if (distance1 < 30 && distance2 < 30) { // 30 cm
        // Both sensors detect a passenger simultaneously, ignore
    } else if (distance1 < 30) {
        // Left sensor detects a passenger
        if (!leftDetected) {
            Slot++;
            leftDetected = true;
            rightDetected = true;
            Serial.println("Passenger detected by sensor 1");
            triggerBuzzer(); // Trigger buzzer when passenger is detected
        }
    } else if (distance2 < 30) {
        // Right sensor detects a passenger
        if (!rightDetected) {
            if (Slot > 0) {
                Slot--;
                leftDetected = true;
                rightDetected = true;
                Serial.println("Passenger detected by sensor 2");
                triggerBuzzer();
                if (Slot == 0) {
                    // Update database and send LoRa message when Slot becomes 0
                    updateDatabase(DEVICE_1_ADDRESS, String(bearing));
                    sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=0");
                    Serial.println("Slot is now 0");
                }
            } else {
                Slot = 0;
                leftDetected = true;
                rightDetected = true;
                Serial.println("Cannot decrement Slot, it's already 0");
            }
        }
    } else {
        // No passenger detected by either sensor
        leftDetected = false;
        rightDetected = false;
    }

    // Send data over LoRa only when Slot has been updated
    if (currentMillis - previousMillis >= interval && (Slot > 0)) {
        previousMillis = currentMillis;
        // Update the database with Slot and bearing
        updateDatabase(DEVICE_1_ADDRESS, String(bearing));
        // Send message to Device 2 (using DEVICE_2_ADDRESS)
        sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=" + String(Slot));
        Serial.print("Sending data: Slot = ");
        Serial.println(Slot);
    }

    // Ensure HTTP request is sent when Slot is 0
    if (Slot == 0) {
        updateDatabase(DEVICE_1_ADDRESS, String(bearing));
    }
}

void triggerBuzzer() {
    for (int i = 0; i < 5; i++) { // Sound the buzzer for a short burst
        digitalWrite(buzzerPin, HIGH);
        delay(100);
        digitalWrite(buzzerPin, LOW);
        delay(100);
    }
}

int calculateBatteryPercentage(float voltage) {
    if (voltage >= 4.2) return 100;
    else if (voltage >= 3.9) return 75;
    else if (voltage >= 3.7) return 50;
    else if (voltage >= 3.5) return 25;
    else return 0;
}

int getDistance(int trigPin, int echoPin) {
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    int duration = pulseIn(echoPin, HIGH);
    int distance = duration * 0.034 / 2; // cm
    return distance;
}

void updateDatabase(int id, const String& value) {
    if (WiFi.status() == WL_CONNECTED) {
        const char* serverUrl = "http://192.168.1.5/jeepfinder/location";
        int currentSlot = (Slot > 0) ? Slot : 0;
        String postData = "ID=" + String(id) + 
                         "&message=" + String(currentSlot) + 
                         "&lat=" + String(gps.location.lat(), 6) + 
                         "&lon=" + String(gps.location.lng(), 6) + 
                         "&rotation=" + String(lastValidBearing);
        if (gps.speed.isValid()) {
            postData += "&speed=" + String(gps.speed.kmph());
        }
        HTTPClient http;
        http.begin(serverUrl);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        int httpResponseCode = http.POST(postData);
        if (httpResponseCode > 0) {
            Serial.print("HTTP Response code: ");
            Serial.println(httpResponseCode);
        } else {
            Serial.println("Error in HTTP request");
        }
        http.end();
    } else {
        Serial.println("WiFi not connected, unable to update database.");
    }
}

void sendLoRaMessage(int receiverAddress, const String& message) {
    LoRa.beginPacket();
    LoRa.write(DEVICE_1_ADDRESS);    // Sender: Device 1 (0x01)
    LoRa.write(DEVICE_2_ADDRESS);    // Receiver: Device 2 (0x03)
    LoRa.print(message);
    LoRa.endPacket();
    Serial.print("Sent message to Device 2: ");
    Serial.println(message);
}
