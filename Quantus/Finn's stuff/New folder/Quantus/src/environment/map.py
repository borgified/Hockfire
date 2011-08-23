from pandac.PandaModules import Filename, NodePath
from sector import Sector, SectorLoader
'''
Created on 11 Jun 2009

@author: finn
'''

class Map():
	'''
	classdocs
	'''
	
	
	
	def __init__(self, name):
		self.sectors = []
		self.currentSector = None
		self.mapRoot = NodePath("Map:" + name)
	
	def addSector(self,sector):
		#TODO: check sector is a Sector
		self.sectors.append(sector)
		sector.getRoot().reparentTo(self.mapRoot)
		sector.hide()
	
	def getRoot(self):
		return self.mapRoot
	
	def getSectors(self):
		return self.sectors
	
	def getCurrentSector(self):
		return self.currentSector
	
	def setCurrentSector(self, sector):
		if self.currentSector != None:
			self.currentSector.hide()
			
		self.currentSector = sector
		
		if self.currentSector != None:
			self.currentSector.show()
		
		
	
	def enable(self):
		if self.currentSector != None:
			self.getCurrentSector.enable()
		
	def disable(self):
		if self.currentSector != None:
			self.getCurrentSector.disable()
		
	def hide(self):
		self.mapRoot.hide()
		self.disable()
		
	def show(self):
		self.mapRoot.show()
		for sector in self.sectors:
			if self.currentSector != sector:
				sector.hide()
		self.enable()

class MapLoader():
		
	def load(self, mapFile):
		map = Map("Map name from " + mapFile)
		
		sectorLoader = SectorLoader(mapFile)
		sector = sectorLoader.load("Sector1")
		
		map.addSector(sector)
		
		sector2 = sectorLoader.load("Sector2")
		
		map.addSector(sector2)
		
		map.setCurrentSector(sector)
		
		return map
	
	def loadFileV1(self):
		pass