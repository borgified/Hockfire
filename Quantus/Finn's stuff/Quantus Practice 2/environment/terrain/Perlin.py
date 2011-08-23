import random
import math
from pandac.PandaModules import PerlinNoise2, PNMImage


class Perlin:
	persistance = 0.5
	freqseed = 1.0
	numberOfOctaves = 8
	smooth = True
	seed = 0
		
	def __init__(self, persistance = 0.5, numberOfOctaves = 8, smooth = True, seed = 0):
		if seed == 0:
			while self.seed == 0:
				random.seed()
				self.seed = random.random()
		else:
			self.seed = seed
		self.pNoise = range(0,numberOfOctaves)
		random.seed(self.seed)
		for i in range(0,numberOfOctaves):
			self.pNoise[i] = PerlinNoise2( 1, 1, 256, random.randint(1,10000))
		self.persistance = persistance
		self.numberOfOctaves = numberOfOctaves
		self.smooth = smooth
		
	def noise1D(self, x):
		total = 0
		
		for i in range(0,self.numberOfOctaves - 1):
			
			frequency = 2**i
			self.freqseed = frequency
			amplitude = self.persistance**i
			
			integer_X    = int(x * frequency)
			fractional_X = (x * frequency) - integer_X
			
			if self.smooth == True:
				v1 = self.smoothedNoise1D(integer_X)
				v2 = self.smoothedNoise1D(integer_X + 1)
			else:
				v1 = self.noiseGen1D(integer_X)
				v2 = self.noiseGen1D(integer_X + 1)
			
			total = total + self.cosineInterpolate(v1 , v2 , fractional_X) * amplitude
			
		return total
	
	def smoothedNoise1D(self,x):
		return self.noiseGen1D(x)/2  +  self.noiseGen1D(x-1)/4  +  self.noiseGen1D(x+1)/4
	
	def noiseGen1D(self,x):
		random.seed(self.freqseed * (1 + x) * self.seed)
		return (random.random() - 0.5) * 2
		#random.seed(self.freqseed * (1 + x) * self.seed)
		#return (random.random() - 0.5) * 2
	
	def linearInterpolate(self,a, b, x):
		return  a*(1-x) + b*x
	
	def cosineInterpolate(self,a, b, x):
		ft = x * math.pi
		f = (1.0 - math.cos(ft)) * .5
		return  a*(1-f) + b*f
	
	def imgNoise2D(self, size = 512, autoOctave = False):
		myImage=PNMImage(1,1)
		myImage.makeGrayscale()
		myImage.setMaxval( (2<<16)-1 )
		myImage.fill(0.5)
		
		octaves = self.numberOfOctaves
		if autoOctave == True:
			octaves = int(math.log(size,2))
			self.pNoise = range(0,octaves)
			random.seed(self.seed)
			for i in range(1,octaves):
				self.pNoise[i] = PerlinNoise2( 1, 1, 256, random.randint(1,10000))
		
		for o in range(1,octaves):
			
			freq = 2**o
			oldImage = myImage
			myImage = PNMImage(freq+1,freq+1)
			myImage.makeGrayscale()
			myImage.setMaxval( (2<<16)-1 )
			myImage.gaussianFilterFrom(1.0, oldImage)
			
			for x in range(0,freq):
				for y in range(0,freq):
					newNoise = (self.pNoise[o].noise( x, y)*(self.persistance**o)) / 2
					myImage.setGray(x,y, myImage.getGray(x,y) + newNoise)#*32)
			for i in range(0,freq+1):
				myImage.setGray(i,freq, myImage.getGray(i%freq,0))
				myImage.setGray(freq,i, myImage.getGray(0,i%freq))
		
		oldImage = myImage
		myImage = PNMImage(size,size)
		myImage.makeGrayscale()
		myImage.setMaxval( (2<<16)-1 )
		myImage.gaussianFilterFrom(1.0, oldImage)
		
		return myImage

	def multiLayerNoise2D(self, x, y):
		total = 0.0
		x = float(x)
		y = float(y)
		for i in range(0,self.numberOfOctaves):
			
			frequency = 2**i
			#self.freqseed = frequency
			amplitude = self.persistance**i
			
			#self.pNoise.setScale(frequency)
			
			integer_X    = int(x * frequency)
			fractional_X = (x * frequency) - integer_X

			integer_Y	= int(y * frequency)
			fractional_Y = (y * frequency) - integer_Y
			
			if self.smooth == True:
				v1 = self.smoothedNoise2D(integer_X,	 integer_Y    , i)
				v2 = self.smoothedNoise2D(integer_X + 1, integer_Y    , i)
				v3 = self.smoothedNoise2D(integer_X,	 integer_Y + 1, i)
				v4 = self.smoothedNoise2D(integer_X + 1, integer_Y + 1, i)
			else:
				v1 = self.pNoise[i].noise(integer_X  ,integer_Y  )
				v2 = self.pNoise[i].noise(integer_X+1,integer_Y  )
				v3 = self.pNoise[i].noise(integer_X  ,integer_Y+1)
				v4 = self.pNoise[i].noise(integer_X+1,integer_Y+1)
			
			i1 = self.cosineInterpolate(v1 , v2 , fractional_X)
			i2 = self.cosineInterpolate(v3 , v4 , fractional_X)
			
			total = total + self.cosineInterpolate(i1 , i2 , fractional_Y) * amplitude
		
		return total
	
	def uniLayerNoise2D(self, x, y):
		v1 = self.pNoise[0].noise(x  ,y  )
		return v1
	
	def smoothedNoise2D(self, x, y, i):
		#self.pNoise.setScale(self.freqseed)
		corners = ( self.pNoise[i].noise(x-1, y-1) + self.pNoise[i].noise(x+1, y-1) + self.pNoise[i].noise(x-1, y+1) + self.pNoise[i].noise(x+1, y+1) ) / 16
		sides   = ( self.pNoise[i].noise(x-1, y  ) + self.pNoise[i].noise(x+1, y  ) + self.pNoise[i].noise(x  , y-1) + self.pNoise[i].noise(x  , y+1) ) / 8
		center  =   self.pNoise[i].noise(x  , y  ) / 4
		
		return center + corners + sides

	
	def noiseGen2D(self, x, y):
		n = (x + y * 57)# + self.seed
		n = (n * 2**13) ^ n
		return 1.0 - ( (n * (n * n * 15731 + 789221) + 1376312589) & 0x7fffffff) / 1073741824.0;	
		#random.seed(self.freqseed * (1 + x**2) * (1 + y/2) * self.seed)
		#return (random.random() - 0.5) * 2
		