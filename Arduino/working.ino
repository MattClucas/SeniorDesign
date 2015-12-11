int TIME_BETWEEN_READINGS_SECONDS = 5;//10*60;// 10 mins* 60 seconds
unsigned long WATERING_TIME_MILLISECONDS = 3 * 1000; // 30 seconds * 1000 ms ~ 27.5 mL

// the water level which turns on the pump to water the plant
int PLANT_WATER_THRESHOLDS[] = {0, 0, 0, 0,
                                0, 0, 0, 0,
                                0, 0, 0, 0,
                                0, 0, 0, 0
                                };
int NUM_PLANTS = 16;

int PLANT_SELECT_PINS[] = {2, 3, 4, 5};
int ENABLE_PUMPS = 8;
int ENABLE_THICKNESS_SENSORS = 7;
int ENABLE_MOISTURE_SENSORS = 6;
int MOISTURE_SENSOR_PIN = A0;
int THICKNESS_SENSOR_PIN = A1;

int currentPlant = 0;
int soil_sensor = 0;
int leaf_sensor = 0;

void setup() {
  Serial.begin(9600);
  pinMode(PLANT_SELECT_PINS[0], OUTPUT);
  pinMode(PLANT_SELECT_PINS[1], OUTPUT);
  pinMode(PLANT_SELECT_PINS[2], OUTPUT);
  pinMode(PLANT_SELECT_PINS[3], OUTPUT);
  digitalWrite(PLANT_SELECT_PINS[0], HIGH);
  digitalWrite(PLANT_SELECT_PINS[1], HIGH);
  digitalWrite(PLANT_SELECT_PINS[2], HIGH);
  digitalWrite(PLANT_SELECT_PINS[3], HIGH);
  
  pinMode(ENABLE_THICKNESS_SENSORS, OUTPUT);
  pinMode(ENABLE_MOISTURE_SENSORS, OUTPUT);
  pinMode(ENABLE_PUMPS, OUTPUT);
  digitalWrite(ENABLE_THICKNESS_SENSORS, LOW);
  digitalWrite(ENABLE_MOISTURE_SENSORS, LOW);
  digitalWrite(ENABLE_PUMPS, HIGH);
}

void loop() {
    // select appropriate plant
    selectPlant(currentPlant);
    
    // read sensors of plant
    soil_sensor = analogRead(THICKNESS_SENSOR_PIN);
    leaf_sensor = analogRead(MOISTURE_SENSOR_PIN);
    boolean turnOnPump = PLANT_WATER_THRESHOLDS[currentPlant] < soil_sensor;

    // write output for plant
    Serial.print("plant_id:");
    Serial.print(currentPlant);
    Serial.print(";soil:");
    Serial.print(soil_sensor);
    Serial.print(";leaf:");
    Serial.print(getThickness(leaf_sensor));
    Serial.print(";watered:");
    Serial.print(turnOnPump);
    Serial.println(";");
    
    // turn on the pump if needed
    if(turnOnPump)
    {
        // turn on pumps
        digitalWrite(ENABLE_PUMPS, LOW);
        delay(WATERING_TIME_MILLISECONDS); 
        digitalWrite(ENABLE_PUMPS, HIGH);
    }

    // increment current plant
    if (currentPlant == NUM_PLANTS-1)
    {
        handleSerialMsg();
        currentPlant = 0;
        int i=0;
        for(;i<TIME_BETWEEN_READINGS_SECONDS;i++)
        {
            delay(1000);
        }
    }
    else
    {
        currentPlant++;
    }
}

void selectPlant(int currentPlant)
{
    digitalWrite(PLANT_SELECT_PINS[3], currentPlant & 8 ? HIGH : LOW);
    digitalWrite(PLANT_SELECT_PINS[2], currentPlant & 4 ? HIGH : LOW);
    digitalWrite(PLANT_SELECT_PINS[1], currentPlant & 2 ? HIGH : LOW);
    digitalWrite(PLANT_SELECT_PINS[0], currentPlant & 1 ? HIGH : LOW);
}

float getThickness(long raw)
{
    return -(2 *(250 * raw - 167919)) / 54887.0;
}

void handleSerialMsg()
{
  while(true)
  {
    Serial.println("Entering handleSerialMsg() while loop");
    String input = "";
    if (Serial.available() > 0) {
      input = Serial.readString();
      Serial.print("Message: ");
      Serial.println(input);
      
      // decide how to handle message based on second byte
      String ack = "";
      if (input.length() > 1)
      {
          switch (input[1])
          {
            case 'W':
              ack = setWaterContent(input);
              break;
            case 'N':
              //ack = setNumberPlants(input);
              break;
            default:
              break;
          }
      }
      
      // acknowledge message to controller
      sendAck(ack);
      
      // break if there is no more to read
      if(input[0] != 'M')
      {
          Serial.println("No more to read.");
          break;
      }
      
      // at this point there is more to read so we wait
      // wait a little bit for the controller to recieve the ack and send more data
      Serial.println("More Data");
      delay(2000);
    }
    // nothing to read, continue on
    else
    {
        Serial.println("Leaving handleSerialMsg()");
        break;
    }
  }
}

void sendAck(String ack)
{
    Serial.println(ack);
}

String setWaterContent(String packet)
{
    Serial.println("Entering setWaterContent()");
    
    // break data into two strings, plant index and water content for that plant
    int indexOfComma = packet.indexOf(',');
    String plantIndexStr = packet.substring(2, indexOfComma);
    String waterContentStr = packet.substring(indexOfComma+1);
    
    Serial.print("plantIndexStr ");
    Serial.print(plantIndexStr);
    Serial.print(", waterContentStr ");
    Serial.println(waterContentStr);
    
    int plantIndex = plantIndexStr.toInt();
    int waterContent = waterContentStr.toInt();
    
    Serial.print("plantIndex ");
    Serial.print(plantIndex);
    Serial.print(", waterContent ");
    Serial.println(waterContent);
    
    if (plantIndex < NUM_PLANTS)
    {
        PLANT_WATER_THRESHOLDS[plantIndex] = waterContent;
    }
    
    int i;
    for(i=0; i<NUM_PLANTS;i++)
    {
      Serial.print("plant:");
      Serial.print(i);
      Serial.print(" thresh:");
      Serial.println(PLANT_WATER_THRESHOLDS[i]);
    }
    
    return "ACK";
}
