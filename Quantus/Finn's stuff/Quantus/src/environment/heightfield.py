from pandac.PandaModules import GeoMipTerrain, Texture, Filename, NodePath
from pandac.PandaModules import TextureStage, TexGenAttrib,VBase4D, Vec3
from pandac.PandaModules import PNMImage,PNMPainter,PNMBrush
from direct.showbase.DirectObject import *
from direct.task.Task import Task
from quantusUtils.Perlin.Perlin import Perlin
from quantusUtils.octree import nodeOctree
from direct.task import Task
import random, math

'''
Created on 11 Jun 2009

@author: finn
'''

MAPSIZE = 1000.0
TEXSIZE = 128.0
HEIGHTSCALE = MAPSIZE*3
HORIZONTALSCALE = MAPSIZE/TEXSIZE

class Heightfield():
	'''
	classdocs
	'''

	
	def __init__(self, name, heightmap = None, bruteforce = True, 
				 blockSize = 32, near = 512, far = 2048):
		self.geomRoot = None
		self.collRoot = None
		self.textures = []
		self.updateTask = None
		self.name = name
		geomip = GeoMipTerrain("Heightfield:" + name)
		
		self.terrainRoot = NodePath("Terrain:" + name)
		
		self.geomip = geomip
		geomRoot = geomip.getRoot()
		self.geomRoot = geomRoot
		geomRoot.reparentTo(self.terrainRoot)
		
		geomRoot.setPos( -MAPSIZE/2, -MAPSIZE/2, -HEIGHTSCALE/2)
		geomRoot.setSx(HORIZONTALSCALE)
		geomRoot.setSy(HORIZONTALSCALE)
		geomRoot.setSz(HEIGHTSCALE)
		
		geomip.setBruteforce(bruteforce)
		geomip.setBlockSize(blockSize)
		geomip.setNear(near)
		geomip.setFar(far)
		
		self.collRoot = self.terrainRoot.attachNewNode("blagablaga")
		
		if heightmap != None:
			self.loadHeightmap(heightmap)
	
	def genCollTree(self):
		
		tempNode = NodePath('tempNode')
		toOctreefy = self.geomRoot.copyTo(tempNode)
		toOctreefy.flattenLight()
		print "begin octreefy"
		collRoot = nodeOctree.octreefy(toOctreefy)
		print "end octreefy"
		tempNode.removeNode()
		self.collRoot.removeNode()
		self.collRoot = collRoot
		collRoot.reparentTo(self.terrainRoot)
	
	def getRoot(self):
		return self.terrainRoot
	
	def getGeomRoot(self):
		return self.GeomRoot
	
	def loadHeightmap(self, heightmap):
		bruteforceChange = False
		if self.geomip.getBruteforce() == False:
			bruteforceChange = True
		
		self.geomip.setHeightfield(heightmap)
		#if bruteforce is off, turn it on for collTree generation
		if bruteforceChange:
			self.geomip.setBruteforce(True)
		print "generating heightfield"
		self.geomip.generate()
		self.geomip.update()
		self.genCollTree()
		if bruteforceChange:
			self.geomip.setBruteforce(False)
			self.geomip.generate()
			self.geomip.update()
		print "heightfield generated"
	
	def addTexture(self, texture, scale = 1.0, name = 'texture'):
		ts = TextureStage(name)
		self.textures.append(ts)
		self.geomRoot.setTexScale(ts, scale, scale)
		self.geomRoot.setTexture(ts, texture, 1 )
		
	def updateFocalPoint(self, task):
		posX = base.camera.getX(render)  + MAPSIZE/2
		posY = base.camera.getY(render)  + MAPSIZE/2
		self.geomip.setFocalPoint(posX, posY)
		self.geomip.update()
		return task.again
	
	def enable(self):
		self.updateTask = taskMgr.doMethodLater(0.1, self.updateFocalPoint, 
												'update_heightfield')
		self.collRoot.reparentTo(self.terrainRoot)
	
	def disable(self):
		if self.updateTask != None:
			taskMgr.remove(self.updateTask)
			self.updateTask = None
		self.collRoot.detachNode()
	
	def hide(self):
		self.terrainRoot.hide()
		
		self.disable()
	
	def show(self):
		self.terrainRoot.show()
		
		self.enable()
	
	def world2MapPos( self, worldPos ):
		result = None
		posX = (worldPos[0] + MAPSIZE/2.0) / HORIZONTALSCALE
		posY = (worldPos[1] + MAPSIZE/2.0) / HORIZONTALSCALE
		result = (posX,posY)
		return result
	
	def getElevation( self, worldPos ):
		result = None
		if abs(worldPos[0]) <= MAPSIZE/2.0 and abs(worldPos[1]) <= MAPSIZE/2.0:
			mapPos = self.world2MapPos(worldPos)
			mapElevation = self.geomip.getElevation( mapPos[0] , mapPos[1] )
			result = (mapElevation * HEIGHTSCALE) - HEIGHTSCALE/2
		return result

class HeightfieldLoader():
	
	def __init__(self, mapFile):
		self.mapFile = mapFile
		
	def load(self, xmlText):
		terrain = Heightfield(xmlText)
		
		size = int(TEXSIZE) + 1
		pb = Perlin( persistance = 0.500, smooth = False, seed = random.random() )
		heightmap = pb.imgNoise2D(size,True)
		
		terrain.loadHeightmap(heightmap)
				
		return terrain
	
	def loadFileV1(self):
		pass