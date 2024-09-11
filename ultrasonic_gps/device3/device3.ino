#include <LoRa.h>
#include <WiFi.h>
#include <HTTPClient.h>

// LoRa pins
#define LORA_SS 15
#define LORA_RST 21
#define LORA_DIO0 26
#define LORA_SCK 22
#define LORA_MISO 19
#define LORA_MOSI 23

// WiFi credentials
const char* ssid = "your-SSID";
const char* password = "your-PASSWORD";

// Server URL
const char* serverUrl = "http://yourserver.com/location.php";

void setup() {
  Serial.begin(9600);

  // Initialize LoRa
  SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_SS);
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
  if (!LoRa.begin(433E6)) {
    Serial.println("Starting LoRa failed!");
    while (1);
  }
  Serial.println("LoRa Initializing OK!");

  // Initialize WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("Connected!");
}

void loop() {
  // Check for incoming LoRa packets
  int packetSize = LoRa.parsePacket();
  if (packetSize) {
    int sender = LoRa.read(); // Read the sender address
    int receiver = LoRa.read(); // Read the receiver address
    String message = "";
    while (LoRa.available()) {
      message += (char)LoRa.read();
    }
    Serial.println("Received message: " + message);

    // Process received data
    processReceivedData(message);
  }
}

void sendLoRaMessage(String message) {
  LoRa.beginPacket();
  LoRa.print(message);
  LoRa.endPacket();
  Serial.println("Sent message: " + message);
}

void processReceivedData(String message) {
  // Process and display the received data
  Serial.println("Processing received data: " + message);

  // Example of extracting data (e.g., battery voltage, GPS coordinates)
  // This should be adapted based on the actual data format
  // Extract ID
  int idIndex = message.indexOf("ID=");
  String id = "";
  if (idIndex != -1) {
    int endIndex = message.indexOf('&', idIndex);
    id = message.substring(idIndex + 3, endIndex);
    Serial.println("Device ID: " + id);
  }

  // Extract message
  int messageIndex = message.indexOf("message=");
  String msg = "";
  if (messageIndex != -1) {
    int endIndex = message.indexOf('&', messageIndex);
    msg = message.substring(messageIndex + 8, endIndex);
    Serial.println("Passenger count: " + msg);
  }

  // Extract latitude
  int latIndex = message.indexOf("lat=");
  String lat = "";
  if (latIndex != -1) {
    int endIndex = message.indexOf('&', latIndex);
    lat = message.substring(latIndex + 4, endIndex);
    Serial.println("Latitude: " + lat);
  }

  // Extract longitude
  int lonIndex = message.indexOf("lon=");
  String lon = "";
  if (lonIndex != -1) {
    int endIndex = message.indexOf('&', lonIndex);
    lon = message.substring(lonIndex + 4, endIndex);
    Serial.println("Longitude: " + lon);
  }

  // Extract speed
  int speedIndex = message.indexOf("speed=");
  String speed = "";
  if (speedIndex != -1) {
    int endIndex = message.indexOf('&', speedIndex);
    speed = message.substring(speedIndex + 6, endIndex);
    Serial.println("Speed: " + speed + " kmph");
  }

  // Extract bearing
  int bearingIndex = message.indexOf("bearing=");
  String bearing = "";
  if (bearingIndex != -1) {
    int endIndex = message.indexOf('&', bearingIndex);
    bearing = message.substring(bearingIndex + 8, endIndex);
    Serial.println("Bearing: " + bearing + " degrees");
  }

  // Extract voltage
  int voltageIndex = message.indexOf("voltage=");
  String voltage = "";
  if (voltageIndex != -1) {
    int endIndex = message.indexOf('&', voltageIndex);
    voltage = message.substring(voltageIndex + 8, endIndex);
    Serial.println("Voltage: " + voltage + " V");
  }

  // Extract battery percentage
  int batteryIndex = message.indexOf("battery=");
  String battery = "";
  if (batteryIndex != -1) {
    battery = message.substring(batteryIndex + 8);
    Serial.println("Battery Percentage: " + battery + " %");
  }

  // Send data to server
  sendToServer(id, msg, lat, lon, speed, bearing);
}

void sendToServer(String id, String msg, String lat, String lon, String speed, String bearing) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "ID=" + id + "&message=" + msg + "&lat=" + lat + "&lon=" + lon + "&speed=" + speed + "&bearing=" + bearing;

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("HTTP Response code: " + String(httpResponseCode));
      Serial.println("Response: " + response);
    } else {
      Serial.println("Error on sending POST: " + String(httpResponseCode));
    }

    http.end();
  } else {
    Serial.println("WiFi Disconnected");
  }
}
