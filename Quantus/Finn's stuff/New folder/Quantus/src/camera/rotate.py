from direct.showbase.DirectObject import *
from pandac.PandaModules import *
from direct.task import Task

'''
Created on 4 Oct 2009

@author: finn
'''

# rotation speed of the camera
ROTATIONSPEED = 50.0

# limit the angle the camera can loop up & down
CAMERALIMIT = (90,-90)

# the cross which is shown in the screen while the cursor is disabled
CROSSTEXTURE = '../data/graphics/camera/cross.png'
AIMCROSSSIZE	= (0.04,0.05)

class Rotation( DirectObject ):
	enabled = False
	def __init__( self , controlledNodeH, controlledNodeP):
		
		# disable the default camera control
		base.disableMouse()
				
		self.controlledNodeH = controlledNodeH
		self.controlledNodeP = controlledNodeP
		
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
		#self.enable()
		
		
	
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
		
		self.accept( 'mouse_toggle', self.toggle )
		
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
		
		self.ignoreAll()
		
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
				currentH = self.controlledNodeH.getH()
				currentP = self.controlledNodeP.getP()
				# add up and down limit for camera
				h = currentH - x * rotationSpeed
				p = max( min( self.controlledNodeP.getP() +  rotationSpeed*y, CAMERALIMIT[0]), CAMERALIMIT[1])
				# add new rotation (mouse movement) to existing one
				self.controlledNodeH.setH(render, h%360)
				self.controlledNodeP.setP(render, p)
		else:
			pass
			#print "camera.cameraRotation.mouseControlTask no mouse found"
		return Task.cont