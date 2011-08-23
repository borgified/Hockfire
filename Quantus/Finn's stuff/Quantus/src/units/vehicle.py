from direct.showbase.DirectObject import *
from unit import Unit
from camera.rotate import Rotation
from camera.vehicleCam import *
from pandac.PandaModules import *
from direct.task import Task
'''
Created on 3 Oct 2009

@author: finn
'''
class Vehicle(Unit):
	'''
	classdocs
	'''


	def __init__(self, team, sig, model, numControllers, keyinput):
		'''
		Constructor
		'''
		self.input = keyinput
		
		self.model = loader.loadModel(model)
		
		tankNode = render.attachNewNode(ActorNode("tank-physics"))
		tankNode.detachNode()
		self.tankNode = tankNode
		actorNode = tankNode.node()
		base.physicsMgr.attachPhysicalNode(actorNode)
		
		actorNode.getPhysicsObject().setMass(1000)
		
		fromObject = tankNode.attachNewNode(CollisionNode('tank-coll'))
		fromObject.node().addSolid(CollisionSphere(0, 0, 0, 1))
		self.model.reparentTo(tankNode)
		
		pusher = PhysicsCollisionHandler()
		pusher.addCollider(fromObject,tankNode)
		
		base.cTrav.addCollider(fromObject, pusher)
		
		fromObject.show()
		
		Unit.__init__(self, sig, numControllers)
		self.enabled = False
		self.camera = VehicleCamera(self.tankNode, Vec3(0,-10,5))
		
		self.rotation = Rotation(self.tankNode, self.camera.camNode)
		
		self.engineFN=ForceNode('tank-engine')
		self.engineFNP=tankNode.attachNewNode(self.engineFN)
		self.upForce=LinearVectorForce(0,0,20000)
		self.upForce.setMassDependent(1)
		self.engineFN.addForce(self.upForce)
		
		base.physicsMgr.addLinearForce(self.upForce)
		
		#self.hoverForceOn = False
	
	def create(self, pos, sector):
		self.sector = sector
		self.tankNode.reparentTo(sector.getRoot())
		self.tankNode.setPos(sector.getRoot(),pos)
	
	def changeController(self, player):
		if self.controllers[0] != None:
			self.remController(self.controllers[0])
		if player.getUnit() != None:
			player.getUnit().remController(player)
		self.addController(player)
		
	def isEnabled(self):
		self.model.reparentTo(render)
		return self.enabled
		
	def toggleEnabled(self):
		if self.isEnabled():
			self.disable()
		else:
			self.enable()
	
	
	
	def enable(self):
		self.enabled = True
		self.camera.enable()
		self.rotation.enable()
		self.hoverTask = self.addTask(self.hoverCheck, "tank hoverCheck")
		
		self.input.setCurrentBranch("general.unit.vehicle.hover.tank")
		
	
	def disable(self):
		self.enabled = False
		self.camera.diable
		self.rotation.disable()
		self.input.setCurrentBranch("general")
		self.removeTask(self.hoverTask)
	
	def hoverCheck(self, task):
		if self.sector != None:
			ground = self.sector.heightfields[0].getElevation(self.tankNode.getPos(self.sector.getRoot()))
			if ground != None:
				amplitude = min((min(self.tankNode.getZ() - (ground + 10),0.0) * -1.0) / 10.0, 0.8)
				#self.upForce.setAmplitude(amplitude)
				#print self.tankNode.node().getPhysicsObject().getVelocity()
		#	if self.tankNode.getZ() < ground + 10:
		#		if self.hoverForceOn == False:
		#			
		#			base.physicsMgr.addLinearForce(self.upForce)
		#			self.hoverForceOn = True
		#	elif self.hoverForceOn == True:
		#		base.physicsMgr.removeLinearForce(self.upForce)
		#		self.hoverForceOn = False
		return task.again
		