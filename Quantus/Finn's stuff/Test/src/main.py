from direct.showbase.DirectObject import *

from src.pandaInteractiveConsole import pandaInteractiveConsole, GUI, CONSOLE
from src.plants import createSmallPlants, createMediumPlants, createLargePlants, createHugePlants
from src.heightfield2 import heightfieldClass, MAPSIZE
from src.roamingPanda import roamingPandaClass
from src.environment import environmentClass
from src.interface import interfaceClass

from pandac.PandaModules import loadPrcFileData, Vec3, Vec4

'''
Finn: LoadPrcFileData allows settings which are normally set in 
a panda config file to be set for this program specifically at runtime
'''
#Finn: sync-video makes the framerate limited to the screen refresh rate
loadPrcFileData("", "sync-video 1" ) 

#Finn: show-frame-rate-meter shows the framerate in the top right 
#(this one isn't documented so worth noting)
loadPrcFileData("", "show-frame-rate-meter 1")

# -- set Fullscreen --
#loadPrcFileData("",  "fullscreen 1")

# -- set window title --
loadPrcFileData("", "window-title Procedural Forest")

import direct.directbase.DirectStart

from src.interactiveConsole.interactiveConsole import pandaConsole, INPUT_CONSOLE, INPUT_GUI, OUTPUT_PYTHON, OUTPUT_IRC
from src.camera import cameraMovement, cameraPicking, cameraRotation, objectIdPickling, cameraAddons, cameraCollision, cameraPicking

class customPickableObject( objectIdPickling.dragDropObject ):
	''' a extension to the pickable object, which sets the object on the ground
	on mouse release
	'''
	def __init__( self, ground, *args, **kwargs ):
		self.ground = ground
		objectIdPickling.dragDropObject.__init__( self , *args, **kwargs )
		self.stopDrag()
	
	def startDrag( self ):
		objectIdPickling.dragDropObject.startDrag(self)
	
	def stopDrag( self ):
		objectIdPickling.dragDropObject.stopDrag(self)
		try:
			radius = self.getBounds().getRadius()
		except:
			radius = 0
		groundHeight = self.ground.get_elevation( [ self.getX(render), self.getY(render) ] )
		self.setZ( render, groundHeight + radius )

	def whileDrag( self, mouseRay ):
		objectIdPickling.dragDropObject.whileDrag( self, mouseRay )
		groundZ = self.ground.get_elevation( [ self.getX(render), self.getY(render) ] )
		# prevent object from being below ground
		if self.getZ( render ) < groundZ:
			self.setZ( render, groundZ )

#Load the first environment model
class the_days_after( DirectObject ):
	def __init__( self ):
		#render.setShaderAuto()
		base.disableMouse()
		# add the interactiveConsole
		
		try:
			import psyco
			psyco.full()
		except ImportError:
			pass

		
		#self.console = pandaConsole( INPUT_CONSOLE|INPUT_GUI|OUTPUT_PYTHON|OUTPUT_IRC, locals() )
		#self.console.toggle() # hide the console by default
		# add a camera rotation controlled by the mouse
		self.cameraRotation = cameraRotation.cameraRotationClass()
		# add a camera movement controlled by the keyboard
		self.cameraMovement = cameraMovement.cameraMovementClass()
		# enable some keys (esc:quit, l:toggleWireframe, p:print camera pos, i:analyze the renderNode, o:change to default mouse controls)
		self.cameraAddons = cameraAddons.cameraAddonsClass()
		# have the mouse drag-dropping objects which have been made pickable
		self.cameraPicking = cameraPicking.cameraPickingClass()
		# add a collision handler for the camera
		#cameraCollision = cameraCollision.cameraCollisionClass()
		
		# create a heightfield (uses 64 geomnodes)
		self.heightfield = heightfieldClass( base.camera )
		
		# add plants onto the heightfield
		self.plantsSmall = createSmallPlants( base.camera, base.camera, self.heightfield ) # 27 geomnodes
		self.plantsMedium = createMediumPlants( base.camera, base.camera, self.heightfield )
		self.plantsLarge = createLargePlants( base.camera, base.camera, self.heightfield )
		self.plantsHuge	= createHugePlants( base.camera, base.camera, self.heightfield )
		
		# add sky
		self.environment = environmentClass( base.camera, self.heightfield )
		# create the compass
		self.interface = interfaceClass()
		''' 
		# create 150 pandas moving around in the world
		for i in range( 150 ):
			panda = roamingPandaClass( self.heightfield, 1000.0, MAPSIZE/2.0 )
			panda.reparentTo( render )
			
		# create a pickable object
		self.pickableModel = customPickableObject( self.heightfield, [objectIdPickling.PICKABLE], 'pickableBox' )
		self.pickableModel.reparentTo( render )
		model = loader.loadModelCopy( 'data/models/box.egg' )
		# if a model is just reparented to the node it is not pickable! (so use attachNewCollidableNode)
		self.pickableModel.attachNewCollidableNode( model.node() )
		'''
		
		# set the camera initially on the heightfield's height
		self.step()
	
	def step( self ):
		# set camera at ground
		base.camera.setZ( render, self.heightfield.get_elevation( [ base.camera.getX( render )
						, base.camera.getY( render ) ] )+5.0 )
		
		#from source.shadowCaster import objectShadowClass
		#objectShadowClass( self.plantsHuge.plantClassNp, self.plantsSmall.plantClassNp ) #self.heightfield.mHeightFieldNode )
		
		# render next frame
		taskMgr.step()

if __name__ == '__main__':
	a = the_days_after()
	while True:
		a.step()