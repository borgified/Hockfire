from direct.showbase.DirectObject import *
from direct.gui.OnscreenImage import OnscreenImage
import Perlin
from pandac.PandaModules import PNMImage
from pandac.PandaModules import Texture
from pandac.PandaModules import Filename
from time import time
import random

class PerlinTest(DirectObject):
	
	
	def __init__(self):
		self.size = 64
		self.p = Perlin.Perlin(numberOfOctaves = 10, persistance = 0.75, smooth = False)
		self.myImage=PNMImage(self.size,self.size)
		self.myImage.makeGrayscale()
		self.myTexture = Texture()
		self.myImage.fill(0.5)
		self.myTexture.load(self.myImage)
		self.imageObject = OnscreenImage(image = self.myTexture, pos = (0, 0, 0))
		self.myList = [None] * (self.size)
		for a in range(self.size):
			self.myList[a] = [0.5] * (self.size)
		taskMgr.add(self.noiseTaskVerySmart, 'perlinNoiseTask')
		self.startTime = time()
		self.noiseTaskVerySmart()
		self.accept("arrow_up", self.run)
		self.i = [None] * (self.size+1)
		for x in range(0,self.size+1):
			self.i[x] = [None] * (self.size+1)


	def run(self):
		for a in range(self.size):
			self.myList[a] = [0.5] * (self.size)
		self.startTime = time()
		taskMgr.add(self.noiseTaskVerySmart, 'perlinNoiseTask')
		
	def noiseTask(self , Task = None):
		numLines = 8
		if Task == None:
			y = 0
		else:
			y = Task.frame * numLines
		
		for yNum in range(0,numLines):
			for x in range(0,self.size):
				i = self.p.noise2D(float(x)/self.size,float(y+yNum)/self.size)
				self.myImage.setGray(x, y+yNum, (i+1.0)/2)
		
		self.myTexture.load(self.myImage)
		if Task != None:
			if self.size >= y + numLines:
				return Task.cont
			else:
				self.myTexture.load(self.myImage)
				print time() - self.startTime
				return Task.done
			
	def noiseTaskSmart(self , Task = None):
		if Task == None:
			o = 0
		else:
			o = Task.frame
		p = Perlin.Perlin(numberOfOctaves = 1, smooth = False, seed = 0)
		freq = 2**o
		for x in range(0,freq+1):
			for y in range(0,freq+1):
				self.i[x][y] = p.intNoise2D( x*freq, y*freq)
		
		for y in range(0,self.size):
			for x in range(0,self.size):
				intX = (x*freq)/self.size
				fraX = (float(x)*freq)/self.size - intX
				intY = (y*freq)/self.size
				i1 = p.linearInterpolate(self.i[intX][intY] , self.i[intX+1][intY] , fraX)
				i2 = p.linearInterpolate(self.i[intX][intY+1] , self.i[intX+1][intY+1] , fraX)
				interNoise = p.linearInterpolate(i1 , i2 , (float(y)*freq)/self.size - intY)
				self.myList[x][y] += interNoise*(0.75**o) / 2
				self.myImage.setGray(x, y, self.myList[x][y])
		
		self.myTexture.load(self.myImage)
		if Task != None:
			if freq < self.size:
				return Task.cont
			else:
				print time() - self.startTime
				return Task.done
		
		
	def noiseTaskVerySmart(self , Task = None):
		if Task == None:
			o = 0
		else:
			o = Task.frame
		p = Perlin.Perlin(numberOfOctaves = 1, smooth = False, seed = 0)
		freq = 2**o
		self.oldImage = self.myImage
		self.myImage = PNMImage(freq+1,freq+1)
		self.myImage.makeGrayscale()
		self.myImage.gaussianFilterFrom(1.0, self.oldImage)

		for x in range(0,freq+1):
			for y in range(0,freq+1):
				self.myImage.setGray(x,y, self.myImage.getGray(x,y) + p.intNoise2D( x*freq, y*freq)*(0.75**o) / 2)
		
		self.myTexture.load(self.myImage)
		if Task != None:
			if freq < self.size:
				return Task.cont
			else:
				print time() - self.startTime
				return Task.done

