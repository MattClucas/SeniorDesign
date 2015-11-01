unsigned long TIME_BETWEEN_READINGS = 10* 60 * 1000; // 10 mins* 60 seconds* 1000 ms //30 seconds
unsigned long WATERING_TIME = 30 * 1000; // 30 seconds * 1000 ms ~ 27.5 mL

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
        delay(WATERING_TIME); 
        digitalWrite(pump_pins[currentPlant], HIGH);
    }

    // increment current plant
    if (currentPlant == NUM_PLANTS-1)
    {
        currentPlant = 0;
        int i=0;
        for(;i<600;i++)
        {
//            Serial.println("waiting");
            delay(1000);
        }
//        unsigned long startTime = millis();
//        while(millis()-startTime < TIME_BETWEEN_READINGS)
//        {
//            delay(1000);
//        }
//        delay(TIME_BETWEEN_READINGS);
    }
    else
    {
        currentPlant++;
    }
}

float getThickness(int raw)
{
    return -(2 *(250 * averageReading - 167919)) / 54887;
}
