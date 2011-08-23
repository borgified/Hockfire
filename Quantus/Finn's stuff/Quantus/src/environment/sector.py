from pandac.PandaModules import NodePath
from heightfield import *
'''
Created on 11 Jun 2009

@author: finn
'''

class Sector():
	'''
	classdocs
	'''
	
	
	def __init__(self, name):
		self.links = []
		self.heightfields = []
		self.sectorRoot = NodePath("Sector:" + name)
	
	def addHeightfield(self, heightfield):
		self.heightfields.append(heightfield)
		heightfield.getRoot().reparentTo(self.sectorRoot)
	
	def getLinks(self):
		return self.links
	
	def getRoot(self):
		return self.sectorRoot
	
	def enable(self):
		for heightfield in self.heightfields:
			heightfield.enable()
	
	def disable(self):
		for heightfield in self.heightfields:
			heightfield.disable()
	
	def hide(self):
		self.sectorRoot.hide()
		self.disable()
		
	def show(self):
		self.sectorRoot.show()
		self.enable()

class SectorLoader():
	
	def __init__(self, mapFile):
		self.mapFile = mapFile
	
	def load(self, xmlText):
		#sector = Sector
		
		sector = Sector(xmlText)
		
		heightfieldLoader = HeightfieldLoader(self.mapFile)
		
		heightfield = heightfieldLoader.load("part of " + xmlText)
		sector.addHeightfield(heightfield)
		
		return sector
	
	def loadFileV1(self):
		pass