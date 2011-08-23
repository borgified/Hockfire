from direct.showbase.DirectObject import *
from player import Player
'''
Created on 3 Oct 2009

@author: finn
'''

class Unit(DirectObject):
	'''
	classdocs
	'''
	
	
	def __init__(self, sig, maxControllers):
		'''
		Constructor
		'''
		self.sig = sig
		self.maxControllers = maxControllers
		self.numControllers = 0
		self.controllers = list()
		
	def addController(self, player, position = None):
		if self.maxControllers == self.numControllers:
			return False
		else:
			self.numControllers += 1
			self.controllers.append((Player,position))
	
	def remController(self, player):
		return False