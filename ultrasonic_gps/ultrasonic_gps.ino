#include <WiFiManager.h>
#include <HTTPClient.h>
#include <TinyGPS++.h>

WiFiManager wifiManager;

const int trigPin1 = 5;    // left Ultrasonic sensor 1 trig pin
const int echoPin1 = 4;    // left Ultrasonic sensor 1 echo pin
const int trigPin2 = 14;   // right Ultrasonic sensor 2 trig pin
const int echoPin2 = 12;   // right Ultrasonic sensor 2 echo pin
const int gpsTxPin = 17;   // Connect NEO-8M TX pin to ESP32 digital pin 17 or rx2
const int gpsRxPin = 16;   // Connect NEO-8M RX pin to ESP32 digital pin 16 or tx2

int Slot = 0;

unsigned long lastUpdateTime = 0;
const unsigned long updateInterval = 1000;

bool leftDetected = false;
bool rightDetected = false;

TinyGPSPlus gps;

void setup() {
  Serial.begin(9600);
  Serial2.begin(9600, SERIAL_8N1, gpsRxPin, gpsTxPin); // Initialize Serial2 for communication with NEO-8M GPS module
  wifiManager.autoConnect("FIND-ME", "");
  wifiManager.autoConnect("FIND-ME");
  wifiManager.autoConnect();
  Serial.println("");
  Serial.println("");
  delay(2000);
  Serial.println();
  pinMode(trigPin1, OUTPUT);
  pinMode(echoPin1, INPUT);
  pinMode(trigPin2, OUTPUT);
  pinMode(echoPin2, INPUT);
}

void loop() {
  unsigned long currentMillis = millis();

  if (currentMillis - lastUpdateTime >= updateInterval) {
    int distance1 = getDistance(trigPin1, echoPin1);
    int distance2 = getDistance(trigPin2, echoPin2);

    while (Serial2.available() > 0) {
      if (gps.encode(Serial2.read())) {
        if (gps.location.isValid()) {
          // Latitude and longitude available in gps.location.lat(), gps.location.lng()
          Serial.print("Latitude: ");
          Serial.println(gps.location.lat(), 6); // Print latitude with 6 decimal places
          Serial.print("Longitude: ");
          Serial.println(gps.location.lng(), 6); // Print longitude with 6 decimal places
        }
        if (gps.speed.isValid()) {
          // Speed available in gps.speed.kmph()
          Serial.print("Speed: ");
          Serial.print(gps.speed.kmph());
          Serial.println(" kmph");
        }
      }
    }

    if (distance1 < 20 && distance2 < 20) {
      // Both sensors detect a passenger simultaneously, ignore
    } else if (distance1 < 20) {
      // Left sensor detects a passenger
      if (!leftDetected) {
        Slot++;
        leftDetected = true;
        rightDetected = true;
        Serial.println("Passenger detected by sensor 1");
      }
    } else if (distance2 < 20) {
      // Right sensor detects a passenger
      if (!rightDetected) {
        Slot--;
        leftDetected = true;
        rightDetected = true;
        Serial.println("Passenger detected by sensor 2");
      }
    } else {
      // No passenger detected by either sensor
      leftDetected = false;
      rightDetected = false;
    }

    updateDatabase(1, Slot);

    Serial.println("Passenger count: " + String(Slot));

    lastUpdateTime = currentMillis;
  }
}

int getDistance(int trigPin, int echoPin) {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  long duration = pulseIn(echoPin, HIGH);
  int distance = duration * 0.034 / 2; // Speed of sound is 34 microseconds per cm
  return distance;
}

void updateDatabase(int id, int value) {
  updateDatabase(id, String(value));
}

void updateDatabase(int id, const String& value) {
  const char* serverUrl = "http://192.168.43.194/jeepfinder/location";
  String postData = "ID=" + String(id) + "&message=" + value + "&lat=" + String(gps.location.lat(), 6) + "&lon=" + String(gps.location.lng(), 6);
  if (gps.speed.isValid()) {
    // Append speed information to the postData string
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
}
