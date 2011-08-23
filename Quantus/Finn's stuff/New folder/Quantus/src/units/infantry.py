from unit import Unit
'''
Created on 3 Oct 2009

@author: finn
'''

class Infantry(Unit):
	'''
	classdocs
	'''


	def __init__(self, player, sig, model, skeleton, animations, inventory):
		'''
		Constructor
		'''
		Unit.__init__(player, sig, numControllers = 1)
		self.player = player
		self.enabled = False
	
	def addController(self, player):
		Unit.addController(player, 0)
		
	def changeController(self, player):
		if self.controllers[0] != None:
			self.remController(self.controllers[0])
		if player.getUnit() != None:
			player.getUnit().remController(player)
		self.addController(player)
		
