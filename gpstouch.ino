#include <TinyGPS++.h>
#include <LoRa.h>
#include <Wire.h>
#include <WiFi.h>
#include <WiFiManager.h>
#include <HTTPClient.h>

// Pin definitions
const int touchPin1 = 5;
const int touchPin2 = 14;
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
// Add this near the top of your file with other global variables
volatile bool loraProcessing = false;
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

float lastValidBearing = 0.0;
bool updateNeeded = false;

unsigned long lastHttpUpdate = 0;
const unsigned long HTTP_UPDATE_INTERVAL = 1000; // 1 second

void setup() {
    Serial.begin(9600);
    Serial2.begin(9600, SERIAL_8N1, gpsRxPin, gpsTxPin);

    // Configure touch pins as INPUT
    pinMode(touchPin1, INPUT);
    pinMode(touchPin2, INPUT);
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
    // First priority: Check for LoRa messages
    int packetSize = LoRa.parsePacket();
    if (packetSize) {
        handleLoRaMessage(packetSize);
        updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
    }

    unsigned long currentMillis = millis();

    // Read digital values from TTP223 sensors
    int touch1 = digitalRead(touchPin1);
    int touch2 = digitalRead(touchPin2);
    
    // Debug prints
    Serial.print("Touch1: ");
    Serial.print(touch1);
    Serial.print(" Touch2: ");
    Serial.println(touch2);

    // Handle passenger detection
    if (touch1 == HIGH && touch2 == HIGH) {
        // Both sensors touched simultaneously, ignore
        Serial.println("Both touched - ignoring");
    } else if (touch1 == HIGH) {
        Serial.println("Touch1 activated");
        if (!leftDetected) {
            Serial.println("Incrementing Slot");
            Slot++;
            leftDetected = true;
            rightDetected = false;
            triggerBuzzer();
            updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
        }
    } else if (touch2 == HIGH) {
        Serial.println("Touch2 activated");
        if (!rightDetected && Slot > 0) {
            Serial.println("Decrementing Slot");
            Slot--;
            leftDetected = false;
            rightDetected = true;
            triggerBuzzer();
            updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
            
            if (Slot == 0) {
                sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=0");
            }
        }
    } else {
        leftDetected = false;
        rightDetected = false;
    }

    // Debug print for Slot value
    Serial.print("Current Slot: ");
    Serial.println(Slot);

    // Third priority: GPS updates
    handleGPS();

    // Fourth priority: Regular LoRa updates
    if (currentMillis - previousMillis >= interval && Slot > 0) {
        previousMillis = currentMillis;
        sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=" + String(Slot));
    }
}

void handleLoRaMessage(int packetSize) {
    int sender = LoRa.read();
    int receiver = LoRa.read();
    
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
                triggerBuzzer();
                // Update HTTP immediately after Slot changes
                updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
                if (Slot == 0) {
                    sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=0");
                }
            }
        } else if (message == "BUTTON2_PRESSED") {
            Slot++;
            triggerBuzzer();
            // Update HTTP immediately after Slot changes
            updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
        }
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

void updateDatabase(int id, const String& value) {
    unsigned long currentTime = millis();
    
    // Only attempt update if enough time has passed since last update
    if (currentTime - lastHttpUpdate < HTTP_UPDATE_INTERVAL) {
        return;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        const char* serverUrl = "https://peachpuff-donkey-807602.hostingersite.com/location";
        int currentSlot = (Slot > 0) ? Slot : 0;
        String postData = "ID=" + String(id) + 
                         "&message=" + String(currentSlot) + 
                         "&lat=" + String(gps.location.lat(), 6) + 
                         "&lon=" + String(gps.location.lng(), 6) + 
                         "&rotation=" + String(lastValidBearing);
        
        if (gps.speed.isValid()) {
            postData += "&speed=" + String(gps.speed.kmph());
        }
        
        http.begin(serverUrl);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        int httpResponseCode = http.POST(postData);
        
        if (httpResponseCode > 0) {
            Serial.print("HTTP Response code: ");
            Serial.println(httpResponseCode);
            lastHttpUpdate = currentTime; // Update timestamp only on successful request
        }
        http.end();
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

// GPS handling function
void handleGPS() {
    unsigned long currentMillis = millis();
    
    if (currentMillis - previousGPSMillis >= gpsInterval) {
        previousGPSMillis = currentMillis;
        
        while (Serial2.available() > 0) {
            if (gps.encode(Serial2.read())) {
                if (gps.location.isValid() && gps.course.isValid()) {
                    float currentLat = gps.location.lat();
                    float currentLng = gps.location.lng();
                    float currentBearing = gps.course.deg();
                    
                    // If GPS data changed, update HTTP immediately
                    if (currentLat != previousLat || 
                        currentLng != previousLng || 
                        currentBearing != previousBearing) {
                        
                        previousLat = currentLat;
                        previousLng = currentLng;
                        previousBearing = currentBearing;
                        lastValidBearing = currentBearing;
                        
                        // Debug print
                        Serial.println("GPS Update:");
                        Serial.print("Lat: "); Serial.println(currentLat, 6);
                        Serial.print("Lng: "); Serial.println(currentLng, 6);
                        Serial.print("Bearing: "); Serial.println(currentBearing);
                        
                        // Update database immediately when GPS data changes
                        updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
                    }
                }
            }
        }
    }
}
