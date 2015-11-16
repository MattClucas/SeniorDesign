#!/usr/bin/python
import serial
import MySQLdb
import smtplib
from email.mime.text import MIMEText
from email.MIMEBase import MIMEBase
from email.MIMEMultipart import MIMEMultipart
conn = MySQLdb.connect(host= "localhost",
                  user="plants",
                  passwd="8SEh2R7LsFQAJnuM",
                  db="plants")
x = conn.cursor()

ser = serial.Serial("/dev/ttyACM0", 9600)

MILLILITERES_PER_SECOND = 55.004 / 60.0
WATERING_SECONDS = 3

# declare globals
waterContent = []
previousWaterContent = []

# generates change packets to send to the arduino
def getPackets():
	packets = []
	for i in range(numPlants):
		if isDiffWaterContent(i):
			packets.append('MW' + str(i) + ',' + str(waterContent[i]))
	# the last packet needs to have a header without the first 'M' so replace the first
	# letter with a 'N'
	if len(packets) > 0:
		packets[-1] = 'N' + packets[-1][1:]
	return packets

# checks if the plant with the given id has had its water content level setting changed
def isDiffWaterContent(plantIndex):
	# handle case for empty previous water content
	if len(previousWaterContent) == 0 and len(waterContent) > 0:
		return True
	return waterContent[plantIndex] != previousWaterContent[plantIndex]

# reads the num_plants.txt file and sets the global numPlants variable
def getNumPlants():
	file = open('settings/num_plants.txt', 'r')
	line = file.readlines()[0]
	file.close()
	if not isInteger(line):
		raise Exception('Number of Plants is not a number! Fix the settings/num_plants.txt file!')
	return int(line)

# get the most recent valid entry from the water_content log file and store it into the waterContent global array
def readWaterContent():
	print 'Entering readWaterContent'
	emailAlert()
	log = open('settings/water_content.txt', 'r')
	lines = log.readlines()
	log.close()
	for i in range(len(lines)-1, -1, -1):
		line = lines[i]
		line = line.split(',')
		if isValidWaterContentEntry(line):
			break
	global waterContent
	global previousWaterContent
	previousWaterContent = waterContent
	waterContent = line
	print ''
	print ''
	print 'Water content'
	print ''.join(str(e) for e in waterContent)
	print 'Leaving readWaterContent'

# checks if every element of the line is an integer, if so it is a valid entry
def isValidWaterContentEntry(line):
	if len(line) is not numPlants:
		return False
	for entry in line:
		entry.strip()
		if not isInteger(entry):
			return False
	return True

# checks if the given string is an integer
def isInteger(str):
	try:
		int(str)
		return True
	except ValueError:
		return False

def insertPlantData(data):
	try:
		data['watered'] = int(data['watered']) * MILLILITERES_PER_SECOND * WATERING_SECONDS
		x.execute("""INSERT INTO PlantMonitor_Data(`PLANT_ID`,`MOISTURE_PERCENTAGE`,`LEAF_THICKNESS`,`WATER_USED_MILLILITERS`) VALUES (%s,%s,%s,%s)""",(data["plant_id"],data["soil"],data["leaf"],data["watered"]))
		conn.commit()
		return True
	except Exception, e:
		print e
		conn.rollback()
		return False	
		
# notify by email when water level is low
def emailAlert():
	# me == the sender's email address
	# you == the recipient's email address
	sender = 'iastateplantalerts@gmail.com'
	receivers = ['clucas@iastate.edu','jamoyer@iastate.edu']
	msg = MIMEMultipart()
	msg['From'] = sender
	msg['To'] = ','.join(receivers)
	msg['Subject'] = 'Low Water Alert'

	msg.attach(MIMEText('Too low of water you noob!'))
	
	# Send the message via our own SMTP server, but don't include the
	# envelope header.
	try:
		s = smtplib.SMTP('smtp.gmail.com', 587)
		s.ehlo()
		s.starttls()
		s.ehlo()
		s.login(sender, 'plantpwpw')
		s.sendmail(sender, ','.join(receivers), msg.as_string())
		s.quit()
		print 'sent email'
	except smtplib.SMTPException, e:
		print e

# initialize number of plants
numPlants = getNumPlants()

# main loop
while 1:
	data = {}
	input_line = ser.readline()
	inputs = input_line.split(";")
	for input in inputs:
		key_val = input.split(":")
		if (len(key_val) == 2):
			data[key_val[0]] = key_val[1]
	print input_line
	if insertPlantData(data):
		# message the arduino to change thresholds
		if data["plant_id"] == str(numPlants - 1):
			# read if there are any changes to the water contents
			readWaterContent();
			# send any changes to the arduino in the form of message packets
			for packet in getPackets():
				ser.write(packet)
				while 1:
					readLine = ser.readline().upper().strip()
					print readLine
					if readLine == 'ACK':
						break

			# now that the packets have been sent, update the previous water content 
			previousWaterContent = waterContent
	
conn.close()

