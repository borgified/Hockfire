from pandac.PandaModules import GeoMipTerrain, Texture, Filename
from pandac.PandaModules import TextureStage, TexGenAttrib,VBase4D, Thread
from direct.showbase.DirectObject import *
from direct.stdpy import thread#threading2 as threading
from direct.task.Task import Task
import lineDrawer
from direct.gui.OnscreenImage import OnscreenImage
from quantusUtils.Perlin import Perlin
from pandac.PandaModules import PNMImage,PNMPainter,PNMBrush
from direct.task import Task
import random, math
#Modified heightfield by Finn, using the more efficient "GeoMipTerrain"
#Not yet working properly, using bruteforce mode


MAPSIZE = 10000.0
TEXSIZE = 1024.0

class Heightfield(DirectObject):# threading.Thread):
	#def __init__(self):
	#	threading.Thread.__init__(self)#, name="test") 
	
	
	def __init__( self ):
		'''try:
			import psyco
			psyco.full()
		except ImportError:
			pass'''
		
		self.ts0 = TextureStage( 'dirtL0' )
		self.ts1 = TextureStage( 'dirtL1' )
		self.ts2 = TextureStage( 'dirtL3' )
		self.tex0 = loader.loadTexture( 'mud-tile.png' )
		
		self.mTerrainHeight = MAPSIZE*3
		self.mHorizontalScale = MAPSIZE/TEXSIZE
		
		size = int(TEXSIZE) + 1
		pb = Perlin.Perlin( persistance = 0.500, smooth = False, seed = random.random() )
		myImage2 = pb.imgNoise2D(size,True)
		
		self.myImage=PNMImage(size,size)
		self.myImage.makeGrayscale()
		self.myImage.setMaxval( (2<<16)-1 )
		
		line = lineDrawer.LineDrawer(self.myImage,(42,180),(13,240),30)
		
		for x in range(size):
			for y in range(size):
				if self.myImage.getGray(x,y) > myImage2.getGray(x,y):
					gray = self.myImage.getGray(x,y) - 0.5
				else:
					gray = myImage2.getGray(x,y) - 0.5
				self.myImage.setGray(x,y,gray + 0.5)
		
		#size = int(TEXSIZE) + 1
		#randSeed = random.random()
		#p1 = Perlin.Perlin( persistance = 0.500, smooth = False, seed = randSeed )
		#self.myImage = p1.imgNoise2D(size,True)
		
		self.terrain1 = GeoMipTerrain("myTerrain1")
		self.terrain2 = GeoMipTerrain("myTerrain2")
		self.setupHeightfield(self.terrain1)
		self.setupHeightfield(self.terrain2)
		
		self.terrain1.getRoot().reparentTo(render)
		self.terrain2.getRoot().reparentTo(render)
		
		self.accept( "g", self.flattenArea)
		self.accept( "u", self.updateWithNewImage)
	
	def setupHeightfield( self , terrain):
		
		terrain.setHeightfield(self.myImage)
		terrain.setBruteforce(True)
		
		terrain.setBlockSize(64)
		terrain.setNear(128)
		terrain.setFar(512)
		terrain.setFocalPoint(base.camera.getPos(render))
		
		taskMgr.add(self.updateTask, "update")
		
		mHeightFieldNode = terrain.getRoot()
		mHeightFieldNode.setPos( -MAPSIZE/2, -MAPSIZE/2, - self.mTerrainHeight/2)
		mHeightFieldNode.setSx(self.mHorizontalScale)
		mHeightFieldNode.setSy(self.mHorizontalScale)
		mHeightFieldNode.setSz(self.mTerrainHeight)
		
		terrain.generate()
		
		scale = 1.0
		mHeightFieldNode.setTexScale( self.ts0, scale, scale )
		mHeightFieldNode.setTexture( self.ts0, self.tex0, 1 )
		
		scale = 32.0
		mHeightFieldNode.setTexScale( self.ts1, scale, scale )
		mHeightFieldNode.setTexture( self.ts1, self.tex0, 1 )
		
		scale = 128.0
		mHeightFieldNode.setTexScale( self.ts2, scale, scale )
		mHeightFieldNode.setTexture( self.ts2, self.tex0, 1 )
		
	
	def flattenArea( self ):
		tilePos = (500,500)
		tileSize = (4000,4000)
		
		imgTilePos = self.world2MapPos(tilePos)
		imgTileSize = self.world2MapPos( (tilePos[0] + tileSize[0], tilePos[1] + tileSize[1]) )
		imgTileSize = (imgTileSize[0] - imgTilePos[0], imgTileSize[1] - imgTilePos[1])
		
		tileSquare = PNMImage(Filename("tile.png"))
		
		tileStamp = PNMImage(int(imgTileSize[0] * (5/3)),int(imgTileSize[1] * (5/3)))
		tileStamp.makeGrayscale()
		tileStamp.addAlpha()
		
		tileStamp.gaussianFilterFrom(1, tileSquare)
		
		count = 4
		total = 0.0
		
		selectXLow = int(imgTilePos[0] + imgTileSize[0] * 0.25)
		selectXHigh = int(imgTilePos[0] + imgTileSize[0] * 0.75)
		selectYLow = int(imgTilePos[1] + imgTileSize[1] * 0.25)
		selectYHigh = int(imgTilePos[1] + imgTileSize[1] * 0.75)
		
		total += self.myImage.getGray(selectXLow,selectYLow)
		total += self.myImage.getGray(selectXLow,selectYLow)
		total += self.myImage.getGray(selectXHigh,selectYHigh)
		total += self.myImage.getGray(selectXHigh,selectYHigh)
		average = total/count
		
		tileStamp.fill(average)
		
		edgeWidth = imgTilePos[0]*(1/3)
		
		self.myImage.blendSubImage(tileStamp, int( imgTilePos[0]-edgeWidth), 
											  int( imgTilePos[1]-edgeWidth), 
										0, 0, int(imgTileSize[0]*( 5/3 )  ), 
											  int(imgTileSize[1]*( 5/3 )  ), 1)
		
	def getCurrentTerrain(self):
		if self.terrain2.getRoot().isHidden():
			return self.terrain1
		else:
			return self.terrain2
	
	def getHiddenTerrain(self):
		if self.terrain1.getRoot().isHidden():
			return self.terrain1
		else:
			return self.terrain2
	
	def updateWithNewImage(self):
		posX = base.camera.getX()  + MAPSIZE/2
		posY = base.camera.getY()  + MAPSIZE/2
		if self.terrain2.getRoot().isHidden():
			self.terrain2.setHeightfield(self.myImage)
			self.terrain2.setFocalPoint(posX, posY)
			if Thread.isThreadingSupported():
				thread.start_new_thread(self.updateWithNewImageThread,(self.terrain2,1))
			else:
				self.updateWithNewImageThread(self.terrain2)
			self.terrain1.getRoot().hide()
			self.terrain2.getRoot().show()
			print "done"
		else:
			self.terrain1.setHeightfield(self.myImage)
			self.terrain1.setFocalPoint(posX, posY)
			if Thread.isThreadingSupported():
				thread.start_new_thread(self.updateWithNewImageThread,(self.terrain1,1))
			else:
				self.updateWithNewImageThread(self.terrain1)
			self.terrain2.getRoot().hide()
			self.terrain1.getRoot().show()
			print "done2"
		
			
	def updateWithNewImageThread(self,terrain,blag=1):
		terrain.update()
	
	def updateTask(self,task):
		posX = base.camera.getX(render)  + MAPSIZE/2
		posY = base.camera.getY(render)  + MAPSIZE/2
		self.getCurrentTerrain().setFocalPoint(posX, posY)
		self.getCurrentTerrain().update()
		return task.cont
	
	def world2MapPos( self, in_pos ):
		result = (0,0)
		if abs(in_pos[0]) <= MAPSIZE/2.0 and abs(in_pos[1]) <= MAPSIZE/2.0:
			posX = (in_pos[0] + MAPSIZE/2.0) / self.mHorizontalScale
			posY = (in_pos[1] + MAPSIZE/2.0) / self.mHorizontalScale
			result = (posX,posY)
		return result
	
	def get_elevation( self, in_pos ):
		result = 0
		if abs(in_pos[0]) <= MAPSIZE/2.0 and abs(in_pos[1]) <= MAPSIZE/2.0:
			posX = (in_pos[0] + MAPSIZE/2.0) / self.mHorizontalScale
			posY = (in_pos[1] + MAPSIZE/2.0) / self.mHorizontalScale
			result = (self.getCurrentTerrain().getElevation(posX ,posY ) * self.mTerrainHeight) - self.mTerrainHeight/2
		
		return result
		
