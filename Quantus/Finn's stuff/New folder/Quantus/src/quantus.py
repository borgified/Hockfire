
from pandac.PandaModules import loadPrcFileData


#try: import _tkinter
#except: sys.exit("Please install python module 'Tkinter'")

'''
Finn: LoadPrcFileData allows settings which are normally set in 
a panda config file to be set for this program specifically at runtime
'''
#Finn: sync-video makes the framerate limited to the screen refresh rate
loadPrcFileData("", "sync-video #t" ) 
#loadPrcFileData("", "basic-shaders-only #f" ) 
#the color depth should be as large as posible (hopefully 24 bits+)
loadPrcFileData("", "color-bits 1" ) 

#the depth buffer should be as large as possible too (hopefully 24 bits+ too)
loadPrcFileData("", "depth-bits 1" ) 
#loadPrcFileData("", "support-threads #f" )
#loadPrcFileData("", "framebuffer-multisample 1" )
#loadPrcFileData("", "multisamples 2" ) 
# -- set Fullscreen --
#loadPrcFileData("",  "fullscreen 1")

# -- set window title --
loadPrcFileData("", "window-title Quantus : Development Branch")

#from pandac.PandaModules import VBase4, NodePath, PandaNode, ActorNode, ForceNode, LinearVectorForce
#from pandac.PandaModules import DirectionalLight, AmbientLight, LightRampAttrib
from pandac.PandaModules import *
from units.player import *
from units.vehicle import *
from units.team import *
from direct.filter.CommonFilters import CommonFilters
from direct.showbase.DirectObject import *
from camera.godMode import GodMode
from environment.map import Map,MapLoader
from input.input import KeymapController
import sys
#This has to be imported after the loadPrcFileData calls or they have no effect
import direct.directbase.DirectStart

#Tank class used for testing
class Tank( DirectObject ):
	def __init__( self ):
		tankModel = loader.loadModel('tank.x')
		#self.tankModel.setScale(10)
		self.accept( "m", self.move )
		
		#tankNode=NodePath("tankNode")
		#tankNode.reparentTo(render)
		
		tankNode = render.attachNewNode(ActorNode("tank-physics"))
		self.tankNode = tankNode
		tankNode.setPos(0,0,5000)
		actorNode = tankNode.node()
		base.physicsMgr.attachPhysicalNode(actorNode)
		
		actorNode.getPhysicsObject().setMass(136.077)
		gravityFN = ForceNode('world-forces')
		gravityFNP = render.attachNewNode(gravityFN)
		gravityForce = LinearVectorForce(0,0,-9.81) #gravity acceleration
		gravityFN.addForce(gravityForce)

		base.physicsMgr.addLinearForce(gravityForce)
		
		
		
		
		fromObject = tankNode.attachNewNode(CollisionNode('tank-coll'))
		fromObject.node().addSolid(CollisionSphere(0, 0, 0, 1))
		tankModel.reparentTo(tankNode)
		
		pusher = PhysicsCollisionHandler()
		pusher.addCollider(fromObject,tankNode)
		
		base.cTrav.addCollider(fromObject, pusher)
		
		fromObject.show()

	def move(self):
		self.tankNode.setY(self.tankNode, 1/self.tankNode.getSy())
	
class Quantus( DirectObject ):
	
	
	def __init__( self ):
		#render.setShaderAuto()
		self.perPixelEnabled = False
		PStatClient.connect()
		
		base.disableMouse()
		base.enableParticles()
		
		render.setAttrib(LightRampAttrib.makeHdr2())
		
		self.input = KeymapController()
		self.input.setCurrentBranch("general.god_mode")
		
		self.dlight = DirectionalLight('dlight')
		self.dlight.setColor(VBase4(0.8, 0.8, 0.5, 1))
		self.dlnp = render.attachNewNode(self.dlight.upcastToPandaNode())
		self.dlnp.setHpr(0, -30, 0)
		
		render.setLight(self.dlnp)
		
		self.alight = AmbientLight('alight')
		self.alight.setColor(VBase4(0.1, 0.1, 0.1, 1))
		self.alnp = render.attachNewNode(self.alight)
		
		render.setLight(self.alnp)
		
		self.filters = CommonFilters(base.win, base.cam)
		self.filters.setBloom()
		#self.filters.setVolumetricLighting(caster=self.dlnp)
		render.setAntialias(AntialiasAttrib.MAuto)
		
		
		traverser = CollisionTraverser('traverser name')
		base.cTrav = traverser
		base.cTrav.setRespectPrevTransform(True)
		
		#self.tank = Tank()
		
		gravityFN = ForceNode('world-forces')
		gravityFNP = render.attachNewNode(gravityFN)
		gravityForce = LinearVectorForce(0,0,-9.81) #gravity acceleration
		gravityFN.addForce(gravityForce)

		base.physicsMgr.addLinearForce(gravityForce)
		angleInt = AngularEulerIntegrator() # Instantiate an AngleIntegrator()
		base.physicsMgr.attachAngularIntegrator(angleInt) # Attatch the AngleIntegrator to the PhysicsManager
		
		team = Team(0, None, "This team rules!")
		self.player = Human(0,0,"ME!!11!!!")
		self.tank = Vehicle(0, 100, "tank.x", 1, self.input)
		self.tank.addController(self.player, 0)
		self.tank.enable()
		
		
		mapLoader = MapLoader()
		self.map = mapLoader.load("Awesome Cool Map.map")
		self.map.getRoot().reparentTo(render)
		
		self.tank.create(Vec3(0,0,5000), self.map.getCurrentSector())
		
		self.godMode = GodMode(self.map, self.input)
		#self.godMode.enable()
		
		self.accept( "switch_sector", self.switchSector )
		self.accept( "god_mode", self.godMode.toggleEnabled )
		self.accept( "menu", sys.exit)
		self.accept( "per_pixel_lighting", self.togglePerPixelLighting )
		
		self.step()
	
	def switchSector(self):
		sectors = self.map.getSectors()
		numSectors = len(sectors)
		for sectorNum in range(0,numSectors):
			if self.map.getCurrentSector() == sectors[sectorNum]:
				self.map.setCurrentSector(sectors[(sectorNum+1)%numSectors])
				break
	
	def togglePerPixelLighting( self ):
		if self.perPixelEnabled:
			self.perPixelEnabled = False
			render.clearShader()
		else:
			self.perPixelEnabled = True
			render.setShaderAuto()
	
	def step( self ):
		# render next frame
		taskMgr.step()

if __name__ == '__main__':
	
	quantus = Quantus()
	#Makes sure that Panda is set to open external windows
	#base.startTk()
	#
	#from direct.tkpanels.ParticlePanel import ParticlePanel
	#pp = ParticlePanel()                        #Create the panel
	#base.setBackgroundColor(0,0,0)
	while True:
		quantus.step()
