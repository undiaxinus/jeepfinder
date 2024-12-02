#include <LoRa.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

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

// I2C Pins
#define SDA_PIN 25
#define SCL_PIN 26

// Define LoRa Addresses
#define DEVICE_1_ADDRESS 0x01
#define DEVICE_2_ADDRESS 0x03  

// Debounce variables
unsigned long lastDebounceTime1 = 0;
unsigned long lastDebounceTime2 = 0;
const unsigned long debounceDelay = 50; // debounce delay in milliseconds

// Initialize the LCD (I2C address: 0x27, 16 chars, 2 lines)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Variable to keep track of the last displayed passenger count
String lastPassengerCount = "";

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

  // Initialize I2C with specified pins
  Wire.begin(SDA_PIN, SCL_PIN);

  // Initialize the LCD
  lcd.init(); // Initialize the LCD
  lcd.backlight(); // Turn on the LCD backlight
  lcd.setCursor(0, 0); // Start writing at the first row
  lcd.print("LoRa Init OK!"); // Display initialization message
  delay(1000);
  lcd.clear();
  
  // Display the initial message
  lcd.print("Passenger count:");
}

void loop() {
  unsigned long currentTime = millis();

  // Button 1 Press (Decrease passenger count)
  if (digitalRead(button1Pin) == LOW && (currentTime - lastDebounceTime1) > debounceDelay) {
    lastDebounceTime1 = currentTime;
    sendLoRaMessage("BUTTON1_PRESSED");  // Send command to Device 1
    lcd.setCursor(0, 1);
    delay(500);
    lcd.clear();
    lcd.print("Passenger count:");
  }

  // Button 2 Press (Increase passenger count)
  if (digitalRead(button2Pin) == LOW && (currentTime - lastDebounceTime2) > debounceDelay) {
    lastDebounceTime2 = currentTime;
    sendLoRaMessage("BUTTON2_PRESSED");  // Send command to Device 1
    lcd.setCursor(0, 1);
    delay(500);
    lcd.clear();
    lcd.print("Passenger count:");
  }

  // Check for incoming LoRa packets
  int packetSize = LoRa.parsePacket();
  
  if (packetSize) {
    Serial.println("Packet received!");
    String message = "";
    int sender = LoRa.read();
    int receiver = LoRa.read();
    
    while (LoRa.available()) {
      message += (char)LoRa.read();
    }
    
    // Check if message is for this device (DEVICE_2_ADDRESS)
    if (receiver == DEVICE_2_ADDRESS) {
      Serial.print("Received message: ");
      Serial.println(message);
      processReceivedData(message);
    } else {
      Serial.println("Message not for this device.");
    }
  }
}

void sendLoRaMessage(String message) {
  LoRa.beginPacket();
  LoRa.write(DEVICE_2_ADDRESS); // Sender: Device 2 (0x03)
  LoRa.write(DEVICE_1_ADDRESS); // Receiver: Device 1 (0x01)
  LoRa.print(message);
  LoRa.endPacket();
  Serial.println("Sent message: " + message);
  lcd.setCursor(0, 0);
  delay(1000);
  lcd.clear();
  lcd.print("Passenger count:"); // Reprint the title after clearing
}

void processReceivedData(String message) {
  Serial.println("Processing received data: " + message);
  
  // Extract slot number from message
  if (message.startsWith("DATA_FROM_DEVICE_1:")) {
    int slotIndex = message.indexOf("Slot=");
    if (slotIndex != -1) {
      String slotNumber = message.substring(slotIndex + 5); 
      Serial.println("Passenger count: " + slotNumber);
      
      // Display sa LCD
      updateLCD("Passenger count:", slotNumber);
    }
  }
}

void updateLCD(String line1, String line2) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(line1);
    lcd.setCursor(0, 1);
    lcd.print(line2);
}
