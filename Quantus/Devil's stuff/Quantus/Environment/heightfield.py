from pandac.PandaModules import GeoMipTerrain, Texture, Filename, TextureStage, TexGenAttrib
from direct.task.Task import Task
from Perlin import Perlin
from pandac.PandaModules import PNMImage
from direct.task import Task
import random
#Modified heightfield by Finn, using the more efficient "GeoMipTerrain"
#Not yet working properly, using bruteforce mode


MAPSIZE = 5000.0
TEXSIZE = 1024.0

class Heightfield:
	def __init__( self, cameraPos ):
		#try:
		#	import psyco
		#	psyco.full()
		#except ImportError:
		#	pass
		self.cameraPos = cameraPos
		self.setupHeightfield()
		taskMgr.doMethodLater(5.0, self.mapUpdateTask, 'UpdateLOD')
	
	def mapUpdateTask( self, task ):
		self.updateHeightField()
		return Task.again
	
	def setupHeightfield( self ):
		
		self.terrain = GeoMipTerrain("mySimpleTerrain")
		
		#p = Perlin.Perlin( persistance = 0.500, smooth = False, seed = 4 )
		#myImage = p.imgNoise2D(int(TEXSIZE)+1,True)
		size = int(TEXSIZE) + 1
		j = random.random()
		p1 = Perlin.Perlin( persistance = 0.500, smooth = False, seed = j )
		#p2 = Perlin.Perlin( persistance = 0.000, smooth = False, seed = j )
		#pb = Perlin.Perlin( persistance = 0.500, smooth = False, seed = random.random() )
		myImage = p1.imgNoise2D(size,True)
		#myImage2 = p2.imgNoise2D(size,True)
		#myImage3 = pb.imgNoise2D(size,True)
		
		#for x in range(size):
		#	for y in range(size):
		#		gray = (myImage.getGray(x,y) - 0.5) * myImage3.getGray(x,y)
		#		gray = gray + (myImage2.getGray(x,y) - 0.5) * (1.0 - myImage3.getGray(x,y))
		#		myImage.setGray(x,y,gray + 0.5)
		self.terrain.setHeightfield(myImage)
		#self.terrain.setBruteforce(True)
		
		self.terrain.setBlockSize(33)
		self.terrain.setFactor(200)#TEXSIZE/(2**4) * 10)
		self.terrain.setFocalPoint(base.camera.getPos())
		self.terrain.getRoot().reparentTo(render)
		#self.terrain.setAutoFlatten(GeoMipTerrain.AFMStrong)
		
		taskMgr.add(self.updateTask, "update")
		
		self.mTerrainHeight = MAPSIZE/20#(TEXSIZE/50)
		self.mHorizontalScale = MAPSIZE/TEXSIZE
		
		self.mHeightFieldNode = self.terrain.getRoot()
		self.mHeightFieldNode.setPos( -MAPSIZE/2, -MAPSIZE/2, 0 )
		self.mHeightFieldNode.setSx(self.mHorizontalScale)
		self.mHeightFieldNode.setSy(self.mHorizontalScale)
		self.mHeightFieldNode.setSz(self.mTerrainHeight)
		
		self.terrain.generate()
		
		self.ts0 = TextureStage( 'dirtL0' )
		self.ts1 = TextureStage( 'dirtL1' )
		self.ts2 = TextureStage( 'dirtL3' )
		
		self.tex0 = loader.loadTexture( 'data/environment/textures/mud-tile.png' )
		
		self.tex0
		#self.ts0.setMode(TextureStage.MAdd)
		scale = 1.0#8.0/1.0
		self.mHeightFieldNode.setTexScale( self.ts0, scale, scale )
		self.mHeightFieldNode.setTexture( self.ts0, self.tex0, 1 )
		
		#self.ts1.setMode(TextureStage.MAdd)
		scale = 32.0/1.0
		self.mHeightFieldNode.setTexScale( self.ts1, scale, scale )
		self.mHeightFieldNode.setTexture( self.ts1, self.tex0, 1 )
		
		#self.ts2.setMode(TextureStage.MAdd)
		scale = 128.0/1.0
		self.mHeightFieldNode.setTexScale( self.ts2, scale, scale )
		self.mHeightFieldNode.setTexture( self.ts2, self.tex0, 1 )
		
		self.updateHeightField()
	
	def updateTask(self,task):
		posX = base.camera.getX()  + MAPSIZE/2
		posY = base.camera.getY()  + MAPSIZE/2
		self.terrain.setFocalPoint(posX, posY)
		self.terrain.update()
		return task.cont
	
	def updateHeightField( self ):
		pass
		''' recalculate heightfield
		
		
		posX, posY = self.world2MapPos( ( self.cameraPos.getX(), self.cameraPos.getY() ) )
		self.mHeightFieldTesselator.setFocalPoint( int(posX), int(posY) )
		
		self.mHeightFieldNode.reparentTo(render) 
		
		
		'''
	
	def world2MapPos( self, in_pos ):
		posX = (in_pos[0] + MAPSIZE/2) / self.mHorizontalScale
		posY = (in_pos[1] + MAPSIZE/2) / self.mHorizontalScale
		return (posX, posY)
	
	def get_elevation( self, in_pos ):
		''' returns the elevation of the heightField at a specific 3d location
		it is not 100% correct...
		'''
		posX = (in_pos[0] + MAPSIZE/2.0) / self.mHorizontalScale
		posY = (in_pos[1] + MAPSIZE/2.0) / self.mHorizontalScale
		return self.terrain.getElevation(posX ,posY ) * self.mTerrainHeight
		
