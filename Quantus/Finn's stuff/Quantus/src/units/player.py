from direct.showbase.DirectObject import *
'''
Created on 3 Oct 2009

@author: finn
'''

class Player(DirectObject):
	'''
	classdocs
	'''
	

	def __init__(self, id, team, name = "player"):
		'''
		Constructor
		'''
		self.unit = None
	
	def getUnit(self):
		return self.unit
	
	def setUnit(self, unit):
		self.unit = unit
	
	def changeUnit(self, unit):
		self.unit.removePlayer(self)
		self.unit.addPlayer(self)
	
class Bot(Player):
	'''
	classdocs
	'''


	def __init__(self, id, team, name = "bot"):
		'''
		Constructor
		'''
		Player.__init__(self, id, team, name)
	
	def isHuman(self):
		return False
	
class Human(Player):
	'''
	classdocs
	'''


	def __init__(self, id, team, name = "player"):
		'''
		Constructor
		'''
		Player.__init__(self, id, team, name)
	
	def isHuman(self):
		return True
	
