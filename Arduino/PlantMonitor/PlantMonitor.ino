int TIME_BETWEEN_READINGS_SECONDS = 5;//10*60;// 10 mins* 60 seconds
unsigned long WATERING_TIME_MILLISECONDS = 3 * 1000; // 30 seconds * 1000 ms ~ 27.5 mL

// the water level which turns on the pump to water the plant
int PLANT_WATER_THRESHOLDS[] = {300, 300, 300, 300};
int NUM_PLANTS = 4;

int pump_pins[] = {2, 3, 4, 5};
int mux_sel0 = 8;
int mux_sel1 = 9;
int mux_sel2 = 10;
int mux_sel3 = 11;
int mux_output = A0;
int mux_output2 = A1;

int currentPlant = 0;
int soil_sensor = 0;
int leaf_sensor = 0;

void setup() {
  Serial.begin(9600);
  pinMode(pump_pins[0], OUTPUT);
  pinMode(pump_pins[1], OUTPUT);
  pinMode(pump_pins[2], OUTPUT);
  pinMode(pump_pins[3], OUTPUT);
  pinMode(mux_sel0, OUTPUT);
  pinMode(mux_sel1, OUTPUT);
  pinMode(mux_sel2, OUTPUT);
  pinMode(mux_sel3, OUTPUT);
  digitalWrite(pump_pins[0], HIGH);
  digitalWrite(pump_pins[1], HIGH);
  digitalWrite(pump_pins[2], HIGH);
  digitalWrite(pump_pins[3], HIGH);
}

void loop() {
    // turn on mux for appropriate plant
    switch(currentPlant) {
      case 0:
        digitalWrite(mux_sel0, LOW);
        digitalWrite(mux_sel1, LOW);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      case 1:
        digitalWrite(mux_sel0, HIGH);
        digitalWrite(mux_sel1, LOW);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      case 2:
        digitalWrite(mux_sel0, LOW);
        digitalWrite(mux_sel1, HIGH);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      case 3:
        digitalWrite(mux_sel0, HIGH);
        digitalWrite(mux_sel1, HIGH);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      default:
        break;
    }
    
    // read sensors of plant
    soil_sensor = analogRead(mux_output);
    leaf_sensor = analogRead(mux_output2);
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
        digitalWrite(pump_pins[currentPlant], LOW);
        delay(WATERING_TIME_MILLISECONDS); 
        digitalWrite(pump_pins[currentPlant], HIGH);
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
