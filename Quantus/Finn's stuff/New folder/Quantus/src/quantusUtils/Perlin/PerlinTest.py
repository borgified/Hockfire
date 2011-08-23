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
		self.myTexture = Texture()
		self.imageObject = OnscreenImage(image = self.myTexture, pos = (0, 0, 0))
		self.run()
		self.accept("arrow_up", self.run)
		
	
	def run(self):
		size = 1024
		j = random.random()
		p1 = Perlin.Perlin( persistance = 0.500, smooth = False, seed = j )
		p2 = Perlin.Perlin( persistance = 0.000, smooth = False, seed = j )
		pb = Perlin.Perlin( persistance = 0.500, smooth = False, seed = random.random() )
		myImage = p1.imgNoise2D(size,True)
		myImage2 = p2.imgNoise2D(size,True)
		myImage3 = pb.imgNoise2D(size,True)
		
		for x in range(size):
			for y in range(size):
				gray = (myImage.getGray(x,y) - 0.5) * myImage3.getGray(x,y)
				gray = gray + (myImage2.getGray(x,y) - 0.5) * (1.0 - myImage3.getGray(x,y))
				myImage.setGray(x,y,gray + 0.5)
		
		self.myTexture.load(myImage)

