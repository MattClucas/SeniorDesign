//constant for time delay of data collection
unsigned int DELAYTIME = 10000;
unsigned long iteration = 1;
//initialize average values
float avg0=0;
float avg1=0;
float avg2=0;
float avg3=0;
// the setup routine runs once when you press reset:
void setup() {
  // initialize serial communication at 9600 bits per second:
  Serial.begin(9600);
}
// the loop routine runs over and over again forever:
void loop() {
  // read the input on analog pin 0:
  int sensorValue0 = analogRead(A0);
  int sensorValue1 = analogRead(A1);
  int sensorValue2 = analogRead(A2);
  int sensorValue3 = analogRead(A3);
  // Convert the analog reading (which goes from 0 - 1023) to a voltage (0 - 5V) and average:
  avg0 = (iteration*avg0+sensorValue0)/(iteration+1);
  avg1 = (iteration*avg1+sensorValue1)/(iteration+1);
  avg2 = (iteration*avg2+sensorValue2)/(iteration+1);
  avg3 = (iteration*avg3+sensorValue3)/(iteration+1);
  if (iteration%DELAYTIME == 0) { 
  // print out the values
  Serial.print("1:");
  Serial.print(avg0);
  Serial.print(",  2:");
  Serial.print(avg1);
  Serial.print(",  3:");
  Serial.print(avg2);
  Serial.print(",  4:");
  Serial.println(avg3);
  
  
  //reset the average values
  avg0=0;
  avg1=0;
  avg2=0;
  avg3=0;
  iteration = 0;
  }
  iteration += 1;
}
