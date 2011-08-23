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
ENGINEPOWER = Vec3(0,0,20000)
ENGINELIST = [  Vec3(-1.2, 1.5, 0) ,
				Vec3( 1.2, 1.5, 0) ,
				Vec3(-1.2,-1.5, 0) ,
				Vec3( 1.2,-1.5, 0)  ]
MAXHOVER = 15
HOVERHIGH = 12
HOVERLOW = 8
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
		
		self.engineFN=ForceNode('tank-hover-engine-front-left')
		self.engineFNP=tankNode.attachNewNode(self.engineFN)
		
		self.engineNodes = [NodePath("front-left-engine") ,
							NodePath("front-right-engine"),
							NodePath("back-left-engine")  ,
							NodePath("back-right-engine")  ]
		
		for i in range(0,4):
			self.engineNodes[i].reparentTo(tankNode)
			self.engineNodes[i].setPos(tankNode, ENGINELIST[i])
			blah = loader.loadModel(model)
			blah.reparentTo(self.engineNodes[i])
			blah.setScale(0.1)
		
		self.linearForce = None
		self.rotateForce = None
		
		self.movement = Movement(tankNode)
		self.movement.enable()
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
		if self.linearForce != None:
			base.physicsMgr.removeLinearForce(self.linearForce)
			self.engineFN.removeForce(self.linearForce)
		if self.rotateForce != None:
			base.physicsMgr.removeAngularForce(self.rotateForce)
			self.engineFN.removeForce(self.rotateForce)
		
		power = [0.0,0.0,0.0,0.0]
		if self.sector != None:
			for i in range(0,4):
				ground = self.sector.heightfields[0].getElevation(self.engineNodes[i].getPos(self.sector.getRoot()))
				if ground != None:
					engineHeight = self.engineNodes[i].getZ(self.sector.getRoot())
					print engineHeight - ground
					if engineHeight < ground + MAXHOVER:
						velocity = self.tankNode.node().getPhysicsObject().getVelocity()
						mass = self.tankNode.node().getPhysicsObject().getMass()
						engineStrength = ENGINEPOWER.getZ() / 4
						maxAccel = engineStrength / mass
						maxRealAccel = maxAccel * ( (ground + MAXHOVER) - engineHeight ) / MAXHOVER
						accel = min((0-velocity.getZ())/4 , maxRealAccel)
						if engineHeight < ground + HOVERHIGH:
							accel = min(accel + 9.81 , maxRealAccel)
						if engineHeight < ground + HOVERLOW and velocity.getZ() < 1:
							accel = maxRealAccel
						power[i] = accel * mass
			
		p = (power[0] + power[1] - power[2]) - power[3]
		r = 0.8 * ((power[0] - power[1]) + power[2] - power[3])
		z = power[0] + power[1] + power[2] + power[3] - (p + r)
		print "frontLeft:",power[0]," frontRight:",power[1]," backLeft:",power[2]," backRight:",power[3],"p:",p," r:",r," z:",z
		self.linearForce=LinearVectorForce(0,0,z)
		self.linearForce.setMassDependent(1)
		self.engineFN.addForce(self.linearForce)
		self.linearForce.setAmplitude(1.0)
		
		self.rotateForce=AngularVectorForce(0,p/1000,r/1000)
		self.engineFN.addForce(self.rotateForce)
		
		base.physicsMgr.addLinearForce(self.linearForce)
		base.physicsMgr.addAngularForce(self.rotateForce)
		
		return task.again

# movement force of the vehicle [forward/reverse, left/right, up/down]
FORCE = Vec3(20000.0, 10000.0, 10000.0)

# directions of movement
DIRECTIONS ={									\
				'RIGHT'		: Vec3( 1, 0, 0),	\
				'LEFT'		: Vec3(-1, 0, 0),	\
				'FORWARD'	: Vec3( 0, 1, 0),	\
				'REVERSE'	: Vec3( 0,-1, 0),	\
				'UP'		: Vec3( 0, 0, 1),	\
				'DOWN'		: Vec3( 0, 0,-1)	\
			}

class Movement( DirectObject ):
	'''
	This camera give to user a god-like freedom to roam the world
	used mainly in development for testing purposes
	
	could be scripted for cinematics?
	'''
	
	
	def __init__( self , tankNode):
		self.actions = dict()
		self.enabled = False
		self.engineFN=ForceNode('tank-engine')
		self.engineFNP=tankNode.attachNewNode(self.engineFN)
		
		for action in DIRECTIONS.items():
			accel = Vec3( FORCE[0] * action[1][0], FORCE[1] * action[1][1], FORCE[2] * action[1][2] )
			upForce=LinearVectorForce(accel)
			upForce.setMassDependent(1)
			self.engineFN.addForce(upForce)
			self.actions[action[1]] = upForce
		
		#self.enable()
	
	def enable( self ):
		self.enabled = True
		# setup key bindings
		controlBindings = {   "move_forward"	: [self.keypress, [DIRECTIONS['FORWARD']]]
							, "move_backward"	: [self.keypress, [DIRECTIONS['REVERSE']]]
							, "move_left"		: [self.keypress, [DIRECTIONS['LEFT']	]]
							, "move_right"		: [self.keypress, [DIRECTIONS['RIGHT']	]]
							, "move_up"			: [self.keypress, [DIRECTIONS['UP']		]]
							, "move_down"		: [self.keypress, [DIRECTIONS['DOWN']	]] }
		
		# set keyboard mappings
		for mapping, [binding, setting] in controlBindings.items():
			if setting is not None:
				self.accept( mapping, binding, setting )
			else:
				self.accept( mapping, binding )
	
	def disable( self ):
		self.enabled = False
		self.ignoreAll()
		taskMgr.remove('movementTask')
	
	def keypress( self, action, enabled ):
		if enabled:
			base.physicsMgr.addLinearForce(self.actions[action])
		else:
			base.physicsMgr.removeLinearForce(self.actions[action])

