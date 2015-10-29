int pump_pins[] = {2, 3, 4, 5};
int mux_sel0 = 8;
int mux_sel1 = 9;
int mux_sel2 = 10;
int mux_sel3 = 11;
int mux_output = A0;
int mux_output2 = A1;

int incomingByte = 0;
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
  if (Serial.available() > 0) {
    incomingByte = Serial.read();
    switch(incomingByte) {
      case '1':
        digitalWrite(mux_sel0, LOW);
        digitalWrite(mux_sel1, LOW);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      case '2':
        digitalWrite(mux_sel0, HIGH);
        digitalWrite(mux_sel1, LOW);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      case '3':
        digitalWrite(mux_sel0, LOW);
        digitalWrite(mux_sel1, HIGH);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      case '4':
        digitalWrite(mux_sel0, HIGH);
        digitalWrite(mux_sel1, HIGH);
        digitalWrite(mux_sel2, LOW);
        digitalWrite(mux_sel3, LOW);
        break;
      default:
        break;
    }
    soil_sensor = analogRead(mux_output);
    leaf_sensor = analogRead(mux_output2);
    Serial.print("Plant #");
    Serial.print(incomingByte - 48);
    Serial.print(": Soil: ");
    Serial.print(soil_sensor);
    Serial.print(" Leaf: ");
    Serial.println(leaf_sensor);
    digitalWrite(pump_pins[incomingByte - 49], LOW);
    delay(5000); 
    digitalWrite(pump_pins[incomingByte - 49], HIGH);
  }
}
