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
const unsigned long HTTP_UPDATE_INTERVAL = 100; // Reduced to 100ms for faster updates

// Add these touch sensor timing constants
const int TOUCH_DELAY = 50;      // Reduced from 100ms to 50ms
const int TOUCH_DURATION = 100;  // Reduced from 200ms to 100ms

// Update these values at the top with other globals
const int GPS_BAUD_RATE = 115200;  // Increased from 9600 for faster GPS data
const unsigned long GPS_READ_INTERVAL = 100;  // Reduced to 100ms for more frequent updates
const int GPS_MAX_AGE = 1000;    // Maximum age of GPS data in milliseconds

// Add these constants at the top
const unsigned long LORA_CHECK_INTERVAL = 1;  // Check every 1ms
unsigned long lastLoRaCheck = 0;

// Add these at the top with other globals
const char* ssid = "AutoConnectAP";  // Your WiFi SSID
const char* password = "";           // Your WiFi password
unsigned long lastWiFiCheck = 0;
const unsigned long WIFI_CHECK_INTERVAL = 5000; // Check WiFi every 5 seconds

// Add at the top with other globals
bool portalRunning = false;

void setup() {
    Serial.begin(115200);
    Serial2.begin(9600, SERIAL_8N1, gpsRxPin, gpsTxPin);
    
    // Configure WiFiManager
    wifiManager.setConfigPortalTimeout(180); // 3 minute timeout
    wifiManager.setConnectTimeout(30); // 30 seconds connection timeout
    
    // Auto connect using saved credentials
    if(!wifiManager.autoConnect("AutoConnectAP")) {
        Serial.println("Failed to connect and hit timeout");
        ESP.restart();
    }
    
    Serial.println("WiFi Connected!");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    
    // Initialize LoRa with optimized settings
    SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_SS);
    LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
    
    if (!LoRa.begin(433E6)) {
        Serial.println("LoRa Failed!");
        while (1);
    }
    
    // Optimize LoRa settings
    LoRa.setSpreadingFactor(7);
    LoRa.setSignalBandwidth(125E3);
    LoRa.setCodingRate4(5);
    LoRa.enableCrc();
    LoRa.setTxPower(20);
    
    pinMode(touchPin1, INPUT);
    pinMode(touchPin2, INPUT);
    pinMode(buzzerPin, OUTPUT);
    pinMode(batteryPin, INPUT);
    
    Serial.println("System Ready!");
}

void checkWiFiConnection() {
    if (WiFi.status() != WL_CONNECTED && !portalRunning) {
        Serial.println("WiFi disconnected, starting portal...");
        portalRunning = true;
        
        // Start config portal with existing wifiManager instance
        if (!wifiManager.startConfigPortal("AutoConnectAP")) {
            Serial.println("Failed to connect and hit timeout");
            delay(3000);
            ESP.restart();
        }
        
        portalRunning = false;
        Serial.println("WiFi Reconnected!");
    }
}

void forceHttpUpdate() {
    static HTTPClient http;
    
    if (WiFi.status() == WL_CONNECTED) {
        const char* serverUrl = "https://peachpuff-donkey-807602.hostingersite.com/location";
        
        String postData = "ID=" + String(DEVICE_1_ADDRESS) + 
                         "&message=" + String(Slot) + 
                         "&lat=" + String(gps.location.lat(), 6) + 
                         "&lon=" + String(gps.location.lng(), 6) + 
                         "&rotation=" + String(lastValidBearing) +
                         "&speed=" + String(gps.speed.kmph());
        
        http.begin(serverUrl);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        http.addHeader("Connection", "keep-alive");
        
        Serial.println("Sending HTTP update...");
        int httpResponseCode = http.POST(postData);
        
        if (httpResponseCode > 0) {
            Serial.println("HTTP Update OK: " + String(httpResponseCode));
        } else {
            Serial.println("HTTP Update Failed: " + String(httpResponseCode));
            checkWiFiConnection(); // Check WiFi if HTTP fails
        }
        http.end();
    } else {
        checkWiFiConnection();
    }
}

void loop() {
    unsigned long currentMillis = millis();
    static unsigned long lastSendMillis = 0;
    const unsigned long SEND_INTERVAL = 250;

    // Always check for incoming LoRa messages first
    int packetSize = LoRa.parsePacket();
    if (packetSize) {
        String message = "";
        int sender = LoRa.read();
        int receiver = LoRa.read();
        
        if (receiver == DEVICE_1_ADDRESS && sender == DEVICE_2_ADDRESS) {
            while (LoRa.available()) {
                message += (char)LoRa.read();
            }
            Serial.println("Received: " + message);

            if (message == "BUTTON1_PRESSED") {
                if (Slot > 0) {
                    Slot--;
                    triggerBuzzer();
                    forceHttpUpdate();
                    
                    if (Slot == 0) {
                        sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=0");
                    }
                }
            } else if (message == "BUTTON2_PRESSED") {
                Slot++;
                triggerBuzzer();
                forceHttpUpdate();
            }
        }
    }

    // Send LoRa status updates
    if (currentMillis - lastSendMillis >= SEND_INTERVAL && Slot > 0) {
        lastSendMillis = currentMillis;
        
        LoRa.beginPacket();
        LoRa.write(DEVICE_1_ADDRESS);
        LoRa.write(DEVICE_2_ADDRESS);
        String message = "DATA_FROM_DEVICE_1: Slot=" + String(Slot);
        LoRa.print(message);
        LoRa.endPacket(true);
        
        Serial.println("Sent: " + message);
    }

    handleTouchSensors();

    // Handle GPS updates
    if (Serial2.available()) {
        if (gps.encode(Serial2.read())) {
            if (gps.location.isValid() && gps.location.age() < GPS_MAX_AGE) {
                float currentLat = gps.location.lat();
                float currentLng = gps.location.lng();
                float currentBearing = gps.course.deg();
                
                if (currentLat != previousLat || 
                    currentLng != previousLng || 
                    currentBearing != previousBearing) {
                    
                    previousLat = currentLat;
                    previousLng = currentLng;
                    previousBearing = currentBearing;
                    lastValidBearing = currentBearing;
                    forceHttpUpdate();
                }
            }
        }
    }
}

void handleTouchSensors() {
    int touch1 = digitalRead(touchPin1);
    int touch2 = digitalRead(touchPin2);

    if (touch1 == HIGH && touch2 == HIGH) {
        Serial.println("Both touched - ignoring");
    } else if (touch1 == HIGH) {
        if (!leftDetected) {
            if (digitalRead(touchPin1) == HIGH) {
                Serial.println("Touch 1 detected - Incrementing Slot");
                Slot++;
                leftDetected = true;
                rightDetected = false;
                triggerBuzzer();
                forceHttpUpdate();  // Immediate update
            }
        }
    } else if (touch2 == HIGH) {
        if (!rightDetected && Slot > 0) {
            if (digitalRead(touchPin2) == HIGH) {
                Serial.println("Touch 2 detected - Decrementing Slot");
                Slot--;
                leftDetected = false;
                rightDetected = true;
                triggerBuzzer();
                forceHttpUpdate();  // Immediate update
                
                if (Slot == 0) {
                    sendLoRaMessage(DEVICE_2_ADDRESS, "DATA_FROM_DEVICE_1: Slot=0");
                }
            }
        }
    } else {
        leftDetected = false;
        rightDetected = false;
    }
}

void triggerBuzzer() {
    for (int i = 0; i < 2; i++) { // Reduced from 5 to 2 beeps
        digitalWrite(buzzerPin, HIGH);
        delay(50);  // Reduced from 100ms to 50ms
        digitalWrite(buzzerPin, LOW);
        delay(50);  // Reduced from 100ms to 50ms
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
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        const char* serverUrl = "https://peachpuff-donkey-807602.hostingersite.com/location";
        int currentSlot = (Slot > 0) ? Slot : 0;
        
        String postData = "ID=" + String(id) + 
                         "&message=" + String(currentSlot) + 
                         "&lat=" + String(gps.location.lat(), 6) + 
                         "&lon=" + String(gps.location.lng(), 6) + 
                         "&rotation=" + String(lastValidBearing) +
                         "&speed=" + String(gps.speed.kmph());
        
        http.begin(serverUrl);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        http.addHeader("Cache-Control", "no-cache");
        
        Serial.println("Updating database - Slot: " + String(currentSlot));
        
        int httpResponseCode = http.POST(postData);
        
        if (httpResponseCode > 0) {
            Serial.println("HTTP Update successful - Response code: " + String(httpResponseCode));
        } else {
            Serial.println("HTTP Update failed - Error: " + String(httpResponseCode));
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
    
    if (currentMillis - previousGPSMillis >= GPS_READ_INTERVAL) {
        previousGPSMillis = currentMillis;
        
        // Read GPS data more aggressively
        while (Serial2.available()) {
            if (gps.encode(Serial2.read())) {
                if (gps.location.isValid() && gps.location.age() < GPS_MAX_AGE) {
                    float currentLat = gps.location.lat();
                    float currentLng = gps.location.lng();
                    float currentBearing = gps.course.deg();
                    float currentSpeed = gps.speed.kmph();
                    
                    // Force update if speed is above threshold or location changed significantly
                    bool significantChange = false;
                    
                    // Check for significant movement (more than 5 meters)
                    if (previousLat != 0 && previousLng != 0) {
                        float distance = TinyGPSPlus::distanceBetween(
                            previousLat, previousLng,
                            currentLat, currentLng
                        );
                        
                        significantChange = (distance > 5) || (currentSpeed > 5);
                    }
                    
                    if (significantChange || 
                        currentLat != previousLat || 
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
                        Serial.print("Speed (km/h): "); Serial.println(currentSpeed);
                        Serial.print("Bearing: "); Serial.println(currentBearing);
                        Serial.print("Fix Age (ms): "); Serial.println(gps.location.age());
                        
                        // Force immediate database update
                        updateDatabase(DEVICE_1_ADDRESS, String(lastValidBearing));
                    }
                }
            }
        }
    }
}
