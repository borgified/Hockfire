from pandac.PandaModules import loadPrcFileData
loadPrcFileData("", "color-bits 1" ) 
from direct.gui.OnscreenImage import OnscreenImage
#import Perlin
import lineDrawer
import Perlin
from pandac.PandaModules import PNMImage
from pandac.PandaModules import Texture
from pandac.PandaModules import Filename
from time import time
import random
from direct.showbase.DirectObject import *
import direct.directbase.DirectStart

class PerlinTest(DirectObject):
	
	
	def __init__(self):
		self.myTexture = Texture()
		self.imageObject = OnscreenImage(image = self.myTexture, pos = (0, 0, 0))
		self.run()
		self.accept("arrow_up", self.run)
		
	
	def run(self):
		size = 256
		pb = Perlin.Perlin( persistance = 0.500, smooth = False, seed = random.random() )
		myImage2 = pb.imgNoise2D(size,True)
		
		myImage=PNMImage(size,size)
		myImage.makeGrayscale()
		myImage.setMaxval( (2<<16)-1 )
		myImage.fill(0.5)
		line = lineDrawer.LineDrawer(myImage,(42,180),(13,253),13)
		
		for x in range(size):
			for y in range(size):
				gray = myImage.getGray(x,y) - 0.5
				gray = gray + (myImage2.getGray(x,y) - 0.5)
				myImage.setGray(x,y,gray + 0.5)
		

		self.myTexture.load(myImage)
		

