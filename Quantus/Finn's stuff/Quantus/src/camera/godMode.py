from pandac.PandaModules import Vec3, CardMaker, NodePath, TextNode
from pandac.PandaModules import TransparencyAttrib, WindowProperties
from direct.showbase.DirectObject import DirectObject
from direct.task import Task
from direct.gui.OnscreenText import OnscreenText
from rotate import Rotation
import direct.directbase.DirectStart
'''
Created on 10 Jun 2009

@author: Finn
based on code from cameraMovement.py in the Jungle Demo
'''
font = loader.loadFont("cmss12")
# Function to put instructions on the screen.
def addInstructions(pos, msg):
    return OnscreenText(text=msg, style=1, fg=(1,1,1,1), font = font,
                        pos=(-1.3, pos), align=TextNode.ALeft, scale = .05)

# Function to put title on the screen.
def addTitle(text):
    return OnscreenText(text=text, style=1, fg=(1,1,1,1), font = font,
                        pos=(1.3,-0.95), align=TextNode.ARight, scale = .07)

class GodMode(DirectObject):
	
	
	def __init__(self, map, input):
		self.enabled = False
		self.map = map
		
		self.input = input
		
		self.texts = []
		self.texts.append(addTitle(""))
		self.texts.append(addInstructions(0.95, ""))
		self.texts.append(addInstructions(0.90, ""))
		self.texts.append(addInstructions(0.85, ""))
		self.texts.append(addInstructions(0.80, ""))
		self.texts.append(addInstructions(0.75, ""))
		
		self.guiPosText = OnscreenText(text = 'position', fg=(1,1,1,1), 
									pos = (-1.3, -0.95), scale = 0.07, 
									mayChange=True, align=TextNode.ALeft )
		
		self.camNode = render.attachNewNode("godCamNode")
		
		self.flyCam = FlyCamera(self.camNode)
		self.groundCam = GroundCamera(self.camNode,self.map)
		
		self.camMode = self.flyCam
		
		
	def switchMode(self):
		self.camMode.disable()
		if self.camMode == self.flyCam:
			self.camMode = self.groundCam
		else:
			self.camMode = self.flyCam
		self.camMode.enable()
	
	def isEnabled(self):
		return self.enabled
		
	def toggleEnabled(self):
		if self.isEnabled():
			self.disable()
		else:
			self.enable()
	
	def enable(self):
		self.input.setCurrentBranch("general.god_mode")
		base.setFrameRateMeter(True)
		self.task = taskMgr.doMethodLater(0.2, self.updateGuiposTask, 'updateGuiposTask')
		self.enabled = True
		self.writeInfo()
		self.camMode.enable()
		self.accept("switch_cam_mode", self.switchMode)
	
	def writeInfo(self):
		self.texts[0].setText("Quantus: In GOD MODE!")
		self.texts[1].setText("[ESC]: Quit")
		self.texts[2].setText("[scroll wheel]: change godcam movement speed")
		self.texts[3].setText("[space]: toggle mouse rotate mode (get cursor back)")
		self.texts[4].setText("[g]: toggle godcam mode (only mode atm, so don't bother)")
		self.texts[5].setText("[j]: switch to another sector")
	
	def disable(self):
		self.input.setCurrentBranch("general")
		base.setFrameRateMeter(False)
		self.ignoreAll()
		self.removeAllTasks()
		taskMgr.remove(self.task)
		self.guiPosText.setText("")
		for text in self.texts:
			text.setText("")
		self.enabled = False
		self.camMode.disable()
	
	def updateGuiposTask( self, task ):
		text = "%s:%s" % (str(base.camera.getPos( render )), str(base.camera.getHpr( render )))
		self.guiPosText.setText( text )
		return Task.again
	
	
	

class FlyCamera(DirectObject):
	enabled = False
	
	def __init__(self,camNode):
		self.camNode = camNode
		
		self.movement = Movement()
		self.rotation = Rotation(self.camNode, self.camNode)
		
	def isEnabled(self):
		return self.enabled
		
	def toggleEnabled(self):
		if self.isEnabled():
			self.disable()
		else:
			self.enable()
	
	def enable(self):
		self.movement.enable()
		self.rotation.enable()
		self.enabled = True
		base.camera.reparentTo( self.camNode )
	
	def disable(self):
		self.movement.disable()
		self.rotation.disable()
		self.enabled = False
		base.camera.reparentTo( render )
		
class GroundCamera(FlyCamera):
	
	def __init__(self, camNode, map):
		FlyCamera.__init__(self, camNode)
		self.map = map
	
	def attachToGroundTask(self, task):
		camPos = (base.camera.getParent().getX(),base.camera.getParent().getY())
		height = self.map.getCurrentSector().heightfields[0].getElevation(camPos)
		if height != None:
			base.camera.getParent().setZ(height + 5)
		return task.again
	
	def enable(self):
		FlyCamera.enable(self)
		taskMgr.add(self.attachToGroundTask, 'attachToGround')
	
	def disable(self):
		FlyCamera.disable(self)
		taskMgr.remove('attachToGround')

# movement speed of the camera [forward/reverse, left/right, up/down]
SPEED = [60.0, 60.0, 60.0]

# directions of movement
DIRECTIONS ={								\
				'RIGHT'		: ( 1, 0, 0),	\
				'LEFT'		: (-1, 0, 0),	\
				'FORWARD'	: ( 0, 1, 0),	\
				'REVERSE'	: ( 0,-1, 0),	\
				'UP'		: ( 0, 0, 1),	\
				'DOWN'		: ( 0, 0,-1)	\
			}

class Movement( DirectObject ):
	'''
	This camera give to user a god-like freedom to roam the world
	used mainly in development for testing purposes
	
	could be scripted for cinematics?
	'''
	
	actions = dict()
	enabled = False
	def __init__( self ):
		self.speed = 1.0

		
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
		self.accept("speed_up",self.faster)
		self.accept("speed_down",self.slower)
		taskMgr.add(self.movementTask, 'movementTask')
	
	def disable( self ):
		self.enabled = False
		self.ignoreAll()
		taskMgr.remove('movementTask')
	
	def faster(self, enabled = None):
		if enabled == None:
			self.speed = self.speed * 1.1
		elif enabled == True:
			pass #todo get this working properly
		elif enabled == False:
			pass
		else:
			raise
	
	def slower(self, enabled = None):
		if enabled == None:
			self.speed = self.speed / 1.1
		elif enabled == True:
			pass #todo get this working properly
		elif enabled == False:
			pass
		else:
			raise
	
	def movementTask( self, task ):
		for movement, enabled in self.actions.items():
			if enabled:
				move = Vec3( self.speed * SPEED[0] * movement[0], self.speed * SPEED[1] * movement[1], self.speed * SPEED[2] * movement[2] )
				base.camera.getParent().setPos( base.camera.getParent(), move * globalClock.getDt() )
		return Task.cont
	
	def keypress( self, action, enabled ):
		self.actions[action] = enabled
		