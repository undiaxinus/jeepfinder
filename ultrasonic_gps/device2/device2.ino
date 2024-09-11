#include <LoRa.h>
#include <Wire.h>
#include <LiquidCrystal.h>

// Button pin definitions
const int button1Pin = 5;
const int button2Pin = 4;

// LoRa pins
#define LORA_SS 15
#define LORA_RST 21
#define LORA_DIO0 32
#define LORA_SCK 22
#define LORA_MISO 19
#define LORA_MOSI 23

// Define LoRa Addresses
#define DEVICE_1_ADDRESS 0x01 
#define DEVICE_3_ADDRESS 0x03

// LCD pin definitions
const int rs = 12, en = 14, d4 = 27, d5 = 17, d6 = 16, d7 = 33;
LiquidCrystal lcd(rs, en, d4, d5, d6, d7);

// LCD dimensions
#define LCD_COLUMNS 16
#define LCD_ROWS 4

// Debounce variables
unsigned long lastDebounceTime1 = 0;
unsigned long lastDebounceTime2 = 0;
const unsigned long debounceDelay = 50; // debounce delay in milliseconds

void setup() {
  Serial.begin(9600);

  pinMode(button1Pin, INPUT_PULLUP);
  pinMode(button2Pin, INPUT_PULLUP);

  // Initialize LoRa
  SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_SS);
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
  if (!LoRa.begin(433E6)) {
    Serial.println("Starting LoRa failed!");
    while (1);
  }
  Serial.println("LoRa Initializing OK!");

  // Initialize LCD
  lcd.begin(LCD_COLUMNS, LCD_ROWS);
  lcd.print("LoRa Initializing");
  lcd.setCursor(0, 1);
  lcd.print("OK!");
  delay(2000); // Pause for 2 seconds
  lcd.clear();
}

void loop() {
  unsigned long currentTime = millis();

  if (digitalRead(button1Pin) == LOW && (currentTime - lastDebounceTime1) > debounceDelay) {
    lastDebounceTime1 = currentTime;
    sendLoRaMessage("BUTTON1_PRESSED");
  }

  if (digitalRead(button2Pin) == LOW && (currentTime - lastDebounceTime2) > debounceDelay) {
    lastDebounceTime2 = currentTime;
    sendLoRaMessage("BUTTON2_PRESSED");
  }

  // Check for incoming LoRa packets
  int packetSize = LoRa.parsePacket();
  if (packetSize) {
    int sender = LoRa.read(); // Read the sender address
    int receiver = LoRa.read(); // Read the receiver address
    String message = "";
    while (LoRa.available()) {
      message += (char)LoRa.read();
    }
    
    // Check if the message is for this device
    if (receiver == DEVICE_3_ADDRESS) {
      Serial.println("Received message: " + message);
      processReceivedData(message);
    }
  }
}

void sendLoRaMessage(String message) {
  LoRa.beginPacket();
  LoRa.write(DEVICE_3_ADDRESS); // Sender address
  LoRa.write(DEVICE_1_ADDRESS); // Receiver address (Device 1)
  LoRa.print(message);
  LoRa.endPacket();
  Serial.println("Sent message: " + message);
}

void processReceivedData(String message) {
  // Process and display the received data
  Serial.println("Processing received data: " + message);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Received Data:");

  // Example of extracting data (e.g., passenger count)
  int messageIndex = message.indexOf("message=");
  if (messageIndex != -1) {
    int endIndex = message.indexOf('&', messageIndex);
    String msg = message.substring(messageIndex + 8, endIndex);
    Serial.println("Passenger count: " + msg);

    lcd.setCursor(0, 1);
    lcd.print("Passenger count:");
    lcd.setCursor(0, 2);
    lcd.print(msg); // Display passenger count
  }
}
