from pandac.PandaModules import Vec3, CardMaker, NodePath, TextNode
from pandac.PandaModules import TransparencyAttrib, WindowProperties
from direct.gui.OnscreenText import OnscreenText
from direct.showbase.DirectObject import DirectObject
from direct.task import Task

'''
Created on 10 Jun 2009

@author: Finn
based on code from cameraMovement.py in the Jungle Demo
'''
class GodCamera(DirectObject):
	enabled = False
	
	def __init__(self):
		self.godCamNode = render.attachNewNode("godCamNode")
		
		
		self.movement = Movement()
		self.rotation = Rotation()
		
		self.guiposOnscreenText = OnscreenText(text = 'position', fg=(1,1,1,1), pos = (-0.9, 0.9), scale = 0.07, mayChange=True, align=TextNode.ALeft )
		
	def isEnabled(self):
		return self.enabled
		
	def toggleEnabled(self):
		if self.isEnabled():
			self.disable()
		else:
			self.enable()
	
	def enable(self):
		taskMgr.doMethodLater(0.1, self.updateGuiposTask, 'updateGuiposTask')
		self.movement.enable()
		self.rotation.enable()
		self.enabled = True
		base.camera.reparentTo( self.godCamNode )
	
	def disable(self):
		self.ignoreAll()
		self.removeAllTasks()
		self.guiposOnscreenText.setText("")
		self.movement.disable()
		self.rotation.disable()
		self.enabled = False
		base.camera.reparentTo( render )
	
	def updateGuiposTask( self, task ):
		text = "%s:%s" % (str(base.camera.getPos( render )), str(base.camera.getHpr( render )))
		self.guiposOnscreenText.setText( text )
		return Task.cont
# movement speed of the camera [forward/reverse, left/right, up/down]
SPEED = [30.0, 30.0, 30.0]

# directions of movement
DIRECTIONS ={								\
				'RIGHT'		: ( 1, 0, 0),	\
				'LEFT'		: (-1, 0, 0),	\
				'FORWARD'	: ( 0, 1, 0),	\
				'REVERSE'	: ( 0,-1, 0),	\
				'UP'		: ( 0, 0, 1),	\
				'DOWN'		: ( 0, 0,-1)	\
			}

BINDINGS = 	{						\
				'FORWARD'	: 'w',	\
				'REVERSE'	: 's',	\
				'LEFT'		: 'a',	\
				'RIGHT'		: 'd',	\
				'UP'		: 'r',	\
				'DOWN'		: 'f'	\
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
		# setup key bindings
		controlBindings = {   BINDINGS['FORWARD'] + ""		: [self.keypress, [DIRECTIONS['FORWARD'], 1]]
							, BINDINGS['FORWARD'] + "-up"	: [self.keypress, [DIRECTIONS['FORWARD'], 0]]
							, BINDINGS['REVERSE'] + ""		: [self.keypress, [DIRECTIONS['REVERSE'], 1]]
							, BINDINGS['REVERSE'] + "-up"	: [self.keypress, [DIRECTIONS['REVERSE'], 0]]
							, BINDINGS['LEFT'] + ""			: [self.keypress, [DIRECTIONS['LEFT']	, 1]]
							, BINDINGS['LEFT'] + "-up"		: [self.keypress, [DIRECTIONS['LEFT']	, 0]]
							, BINDINGS['RIGHT'] + ""		: [self.keypress, [DIRECTIONS['RIGHT']	, 1]]
							, BINDINGS['RIGHT'] + "-up"		: [self.keypress, [DIRECTIONS['RIGHT']	, 0]]
							, BINDINGS['UP'] + ""			: [self.keypress, [DIRECTIONS['UP']		, 1]]
							, BINDINGS['UP'] + "-up"		: [self.keypress, [DIRECTIONS['UP']		, 0]]
							, BINDINGS['DOWN'] + ""			: [self.keypress, [DIRECTIONS['DOWN']	, 1]]
							, BINDINGS['DOWN'] + "-up"		: [self.keypress, [DIRECTIONS['DOWN']	, 0]] }
		
		# set keyboard mappings
		for mapping, [binding, setting] in controlBindings.items():
			if setting is not None:
				self.accept( mapping, binding, setting )
			else:
				self.accept( mapping, binding )
		
		self.accept("wheel_up",self.faster)
		self.accept("wheel_down",self.slower)
		
		self.enable()
	
	def enable( self ):
		self.enabled = True
		taskMgr.add(self.movementTask, 'movementTask')
	
	def disable( self ):
		self.enabled = False
		taskMgr.remove('movementTask')
	
	def faster(self):
		self.speed = self.speed * 1.1
	
	def slower(self):
		self.speed = self.speed / 1.1
	
	def movementTask( self, task ):
		for movement, enabled in self.actions.items():
			if enabled:
				movement = Vec3( self.speed * SPEED[0] * movement[0], self.speed * SPEED[1] * movement[1], self.speed * SPEED[2] * movement[2] )
				base.camera.getParent().setPos( base.camera.getParent(), movement * globalClock.getDt() )
		return Task.cont
	
	def keypress( self, action, enabled ):
		self.actions[action] = enabled

# rotation speed of the camera
ROTATIONSPEED = 50.0

# limit the angle the camera can loop up & down
CAMERALIMIT = (90,-90)

# the cross which is shown in the screen while the cursor is disabled
CROSSTEXTURE = 'camera/cross.png'
AIMCROSSSIZE	= (0.04,0.05)

class Rotation( DirectObject ):
	enabled = False
	def __init__( self ):
		
		# disable the default camera control
		base.disableMouse()
		
		# create the aiming cross
		cm = CardMaker('aim-cross')
		cm.setFrame(-AIMCROSSSIZE[0],AIMCROSSSIZE[0],-AIMCROSSSIZE[1],AIMCROSSSIZE[1])
		cross = cm.generate()
		self.aimingCross = NodePath(cross)
		# set texture to cross
		tex = loader.loadTexture(CROSSTEXTURE)
		self.aimingCross.setTexture(tex)
		# enable transparency on the aiming cross
		self.aimingCross.setTransparency(TransparencyAttrib.MAlpha)
		self.aimingCross.detachNode()
		
		# enable this class functions
		self.enable()
		
		self.accept( 'space', self.toggle )
	
	def toggle( self ):
		if self.enabled:
			self.disable()
		else:
			self.enable()
	
	def enable( self ):
		''' enable first person camera rotation by mouse and bind/hide the mouse
		'''
		# the mouse is not centered
		self.mouseCentered = False
		# enable the mouse rotation task
		taskMgr.add(self.rotationTask, 'cameraRotationTask')
		
		# hide mouse cursor
		wp = WindowProperties()
		wp.setCursorHidden(True)
		# does not exist panda 1.3.2 / but is reqired for osx-mouse movement
		try: wp.setMouseMode(WindowProperties.MRelative)
		except: pass
		base.win.requestProperties(wp)
		
		# add the cross to the window
		self.aimingCross.reparentTo( render2d )
		
		self.enabled = True
	
	def disable( self ):
		''' disable first person camera rotation of mouse and free/show the cursor
		'''
		self.rotationSpeed = 0
		# disable the mouse rotation task
		taskMgr.remove('cameraRotationTask')
		
		# show mouse cursor
		wp = WindowProperties()
		wp.setCursorHidden(False)
		# does not exist panda 1.3.2 / but is reqired for osx-mouse movement
		try: wp.setMouseMode(WindowProperties.MAbsolute)
		except: pass
		base.win.requestProperties(wp)
		
		# remove the cross from the window
		self.aimingCross.detachNode()
		
		self.enabled = False
	
	def setMouseCentered( self, task=None ):
		# reset mouse position to the center of the window
		base.win.movePointer(0, base.win.getXSize()/2, base.win.getYSize()/2)
	
	def rotationTask( self, task ):
		# if we can find a mouse
		if base.mouseWatcherNode.hasMouse():
			# we want to center the mouse on the first frame
			if not self.mouseCentered:
				self.setMouseCentered()
				self.mouseCentered = True
			else:
				# get mouse position
				mpos = base.mouseWatcherNode.getMouse()
				x, y = mpos.getX(), mpos.getY()
				if x!=0 or y!=0:
					# reset mouse position to the center of the window
					self.setMouseCentered()
				# calculate movement force
				rotationSpeed = ROTATIONSPEED # * globalClock.getDt()
				# get current rotation
				currentHpr = base.camera.getParent().getHpr()
				# add up and down limit for camera
				h = currentHpr.getX() - x * rotationSpeed
				p = max( min( currentHpr.getY() +  rotationSpeed*y, CAMERALIMIT[0]), CAMERALIMIT[1])
				# add new rotation (mouse movement) to existing one
				base.camera.getParent().setHpr( render, h%360, p, 0)
		else:
			pass
			#print "camera.cameraRotation.mouseControlTask no mouse found"
		return Task.cont