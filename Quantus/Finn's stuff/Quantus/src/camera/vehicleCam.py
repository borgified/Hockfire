from direct.showbase.DirectObject import *
from pandac.PandaModules import NodePath
from pandac.PandaModules import CompassEffect
'''
Created on 3 Oct 2009

@author: finn
'''

class VehicleCamera(DirectObject):
	'''
	classdocs
	'''
	
	
	def __init__(self, vehicleNode, distance):
		'''
		Constructor
		'''
		self.enabled = False
		
		self.vehicleNode = vehicleNode
		self.centerNode = NodePath("centerNode")
		self.camNode = NodePath("cameraNode")
		self.centerNode.reparentTo(vehicleNode)
		self.camNode.reparentTo(vehicleNode)
		compass = CompassEffect.make(render)
		self.centerNode.setEffect(compass)
		self.camNode.setPos(self.centerNode, distance)
		self.camNode.lookAt(self.centerNode)
		
	def isEnabled(self):
		return self.enabled
		
	def toggleEnabled(self):
		if self.isEnabled():
			self.disable()
		else:
			self.enable()
	
	def enable(self):
		self.enabled = True
		base.camera.reparentTo( self.camNode )
		print "WAGA"
		self.task = taskMgr.add(self.rotateTask, 'updateGuiposTask')
	
	def disable(self):
		self.enabled = False
		base.camera.reparentTo( render )
	
	def rotateTask(self, task):
		
		self.myInterval = self.centerNode.hprInterval(1.0,self.vehicleNode.getHpr())
		return task.again
		