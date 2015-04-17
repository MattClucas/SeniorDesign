//constant for time delay of data collection
unsigned int DELAYTIME = 10000;
unsigned long iteration = 1;
//define vairables
#define MS_PIN A0
#define TS_PIN A1
#define MC_PIN 13
#define NUM_ITERATIONS 10000
#define MOISTURE_THRESHOLD 0
#define MILI_PER_SECOND 1
#define DELAY_TIME 1000

int moistureContent=0;
int waterUsed=0;
long moistureReading=0;
long temperatureReading=0;
char** piSignal;
// the setup routine runs once when you press reset:
void setup() {
  pinMode(MC_PIN, OUTPUT);
  // initialize serial communication at 9600 bits per second:
  Serial.begin(9600);
}

void loop() {
  //TODO Wait for pi signal message
  //piSignal = fancyReadFromPi;
  takeReading(&moistureReading, &temperatureReading);
  if (recievedMessage())
  {
    if (strcmp(piSignal[0],"READ"))
    {
        sendToPi(moistureReading, temperatureReading, waterUsed);
        waterUsed = 0;
    }
    else if(strcmp(piSignal[0],"APPLY_NEW_MOISTURE_CONTENT")==0)
    {
        moistureContent = atoi(piSignal[1]);
    }
  }
  if(needsWater(moistureReading))
  {
    waterUsed += addWater(moistureReading);
  }
  
  delay(DELAY_TIME);
}

char recievedMessage()
{
   return 1;//TODO
}

void takeReading(long *moistureReading, long *temperatureReading)
{
  long prevMoistureVal = -90;
  *moistureReading = 0;
  *temperatureReading = 0;
  int i;
  while(!averagingThreshold(prevMoistureVal, *moistureReading))
  {   
    prevMoistureVal = *moistureReading;
    for(i = 0; i < NUM_ITERATIONS ; i++)
    { 
      *moistureReading += analogRead(MS_PIN);
      *temperatureReading += analogRead(TS_PIN);
    }
    *moistureReading = *moistureReading/NUM_ITERATIONS;
    *temperatureReading = *temperatureReading/NUM_ITERATIONS;
  }
  *moistureReading = rawToMoisturePercentage(*moistureReading);
  *temperatureReading = rawToTemperaturePercentage(*temperatureReading);
}

char averagingThreshold(long prev, long current)
{
  if((current < prev + 1) && (current > prev - 1))
  {
    return 1;
  }
  return 0;
}

char needsWater(int moistureReading)
{
  if(moistureReading < moistureContent - MOISTURE_THRESHOLD)
  {
    return 1;
  }
  return 0;    
}

int addWater(int moistureReading)
{
  int neededWater = moistureContent - moistureReading;
  int wateringTime = calculateWateringTime(neededWater);
  //Activate solenoid for wateringTime ms
  digitalWrite(MC_PIN, HIGH);
  delay(wateringTime);
  digitalWrite(MC_PIN, LOW);
  
  return calculateWateringAmount(wateringTime);
}

int calculateWateringTime(int neededWater)
{
  return 0;//TODO
}

int calculateWateringAmount(int wateringTime)
{
  return (MILI_PER_SECOND*wateringTime)/1000;
}

int rawToMoisturePercentage(int moistureRAW)
{
  float temp = (float) moistureRAW;
  if(moistureRAW < 591)
  {
    return((196727 + 1000 * temp) / 92257);
  }
  return((-572903+1000 * temp)/6264);
}

int rawToTemperaturePercentage(int temperatureRAW)
{
  float temp = (float)temperatureRAW;
  return .211 * temp + 45.843;
}

void sendToPi(long moistureReading, long temperatureReading, int waterUsed)
{
      Serial.print("moisture ");
  Serial.println(moistureReading);
      Serial.print("temperature ");
  Serial.println(temperatureReading);
      Serial.print("h20 ");
  Serial.println(waterUsed);
  Serial.println();
}
