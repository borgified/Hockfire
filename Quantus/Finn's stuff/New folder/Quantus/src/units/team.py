from direct.showbase.DirectObject import *
'''
Created on 3 Oct 2009

@author: finn
'''

class Team(object):
	'''
	classdocs
	'''


	def __init__(self, id, color, name="team"):
		'''
		Constructor
		'''
		self.players = list()
		
	def addPlayer(self, player):
		self.players.append(player)
		
	def remPlayer(self, player):
		self.players.remove(player)