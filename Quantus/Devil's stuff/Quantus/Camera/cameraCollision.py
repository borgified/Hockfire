from pandac.PandaModules import CollisionRay, CollisionNode, CollisionTraverser, CollisionHandlerQueue, NodePath, Vec3
from direct.task import Task

from objectIdPickling import GROUND, WALLS, bitMaskOr

GROUNDDISTANCE = 3.5
MINDISTANCEWALL = 1.75

class CameraCollision:
	def __init__( self ):
		self.collisionCheckSetup()
		
	def collisionCheckSetup( self ):
		print "setting up collision check"
		#No we create a ray to start above the ball and cast down. This is to
		#Determine the height the ball should be at and the angle the floor is
		#tilting. We could have used the sphere around the ball itself, but it
		#would not be as reliable
		self.cameraGroundRay = CollisionRay()		 #Create the ray
		self.cameraGroundRay.setOrigin(0,0,0.0)		#Set its origin
		self.cameraGroundRay.setDirection(0,0,-1.0) #And its direction
		#Collision solids go in CollisionNode
		self.cameraGroundCol = CollisionNode('cameraGroundRay') #Create and name the node
		self.cameraGroundCol.addSolid(self.cameraGroundRay) #Add the ray
		self.cameraGroundCol.setFromCollideMask(bitMaskOr([GROUND])) #Set its bitmasks
		self.cameraGroundCol.setIntoCollideMask(bitMaskOr([]))
		#Attach the node to the ballRoot so that the ray is relative to the ball
		#(it will always be 10 feet over the ball and point down)
		#self.cameraGroundColNp = base.camera.attachNewNode(self.cameraGroundCol)
		### the ground controller is allways looking down NOT ACTIVE
		self.horizontalCameraNode = base.camera.attachNewNode('horizontalCameraNode')
		self.horizontalCameraNode.reparentTo( base.camera )
		self.cameraGroundColNp = self.horizontalCameraNode.attachNewNode(self.cameraGroundCol)
		
		#Uncomment this line to see the ray
		#self.cameraGroundColNp.show()
		'''
		# the camera forward rays look in the direction of the camera
		self.cameraFrontRay = CollisionRay()		 #Create the ray
		self.cameraFrontRay.setOrigin	 (0,-1,0)		#Set its origin
		self.cameraFrontRay.setDirection(0, 5,0) #And its direction
		self.cameraFrontCol = CollisionNode('cameraFrontRay') #Create and name the node
		self.cameraFrontCol.addSolid(self.cameraFrontRay) #Add the ray
		self.cameraFrontCol.setFromCollideMask(bitMaskOr([WALLS])) #Set its bitmasks
		self.cameraFrontCol.setIntoCollideMask(bitMaskOr([]))
		self.cameraFrontColNp = base.camera.attachNewNode(self.cameraFrontCol)
		#self.cameraFrontColNp.show()
		
		self.cameraBackRay = CollisionRay()		 #Create the ray
		self.cameraBackRay.setOrigin	 (0, 1,0)		#Set its origin
		self.cameraBackRay.setDirection(0,-5,0) #And its direction
		self.cameraBackCol = CollisionNode('cameraBackRay') #Create and name the node
		self.cameraBackCol.addSolid(self.cameraBackRay) #Add the ray
		self.cameraBackCol.setFromCollideMask(bitMaskOr([WALLS])) #Set its bitmasks
		self.cameraBackCol.setIntoCollideMask(bitMaskOr([]))
		self.cameraBackColNp = base.camera.attachNewNode(self.cameraBackCol)
		#self.cameraBackColNp.show()
		
		# the camera left/right rays
		self.cameraLeftRay = CollisionRay()		 #Create the ray
		self.cameraLeftRay.setOrigin	 (-1,0,0)		#Set its origin
		self.cameraLeftRay.setDirection( 5,0,0) #And its direction
		self.cameraLeftCol = CollisionNode('cameraLeftRay') #Create and name the node
		self.cameraLeftCol.addSolid(self.cameraLeftRay) #Add the ray
		self.cameraLeftCol.setFromCollideMask(bitMaskOr([WALLS])) #Set its bitmasks
		self.cameraLeftCol.setIntoCollideMask(bitMaskOr([]))
		self.cameraLeftColNp = base.camera.attachNewNode(self.cameraLeftCol)
		#self.cameraLeftColNp.show()
		
		self.cameraRightRay = CollisionRay()		 #Create the ray
		self.cameraRightRay.setOrigin	 ( 1,0,0)		#Set its origin
		self.cameraRightRay.setDirection(-5,0,0) #And its direction
		self.cameraRightCol = CollisionNode('cameraRightRay') #Create and name the node
		self.cameraRightCol.addSolid(self.cameraRightRay) #Add the ray
		self.cameraRightCol.setFromCollideMask(bitMaskOr([WALLS])) #Set its bitmasks
		self.cameraRightCol.setIntoCollideMask(bitMaskOr([]))
		self.cameraRightColNp = base.camera.attachNewNode(self.cameraRightCol)
		#self.cameraRightColNp.show()
		'''
		
		#Finally, we create a CollisionTraverser. CollisionTraversers are what
		#do the job of calculating collisions
		self.cTrav = CollisionTraverser()
		#Collision traverservs tell collision handlers about collisions, and then
		#the handler decides what to do with the information. We are using a
		#CollisionHandlerQueue, which simply creates a list of all of the
		#collisions in a given pass. There are more sophisticated handlers like
		#one that sends events and another that tries to keep collided objects
		#apart, but the results are often better with a simple queue
		self.cGroundHandler = CollisionHandlerQueue()
		self.cWallHandler = CollisionHandlerQueue()
		#Now we add the collision nodes that can create a collision to the
		#traverser. The traverser will compare these to all others nodes in the
		#scene. There is a limit of 32 CollisionNodes per traverser
		#We add the collider, and the handler to use as a pair
		#self.cTrav.addCollider(self.cameraBallSphere, self.cHandler)
		self.cTrav.addCollider(self.cameraGroundColNp, self.cGroundHandler)
		'''
		self.cTrav.addCollider(self.cameraBackColNp, self.cWallHandler)
		self.cTrav.addCollider(self.cameraFrontColNp, self.cWallHandler)
		self.cTrav.addCollider(self.cameraLeftColNp, self.cWallHandler)
		self.cTrav.addCollider(self.cameraRightColNp, self.cWallHandler)
		'''
		# we dont want this to be automatically executed
		#base.cTrav = self.cTrav
		
		#Collision traversers have a built in tool to help visualize collisions.
		#Uncomment the next line to see it.
		#self.cTrav.showCollisions(render)
		
		#self.cTrav.traverse( render )
		
		# add a task to check for collisions
		taskMgr.add(self.collisionCheckTask, 'collisionCheckTask')
	
	def collisionCheckTask( self, task ):
		# make the parent of the groundCollideHandler be horizontal relative to render
		self.horizontalCameraNode.setHpr( render, Vec3(0,0,0))
		
		self.cTrav.traverse( render )
		
		#The collision handler collects the collisions. We dispatch which function
		#to handle the collision based on the name of what was collided into
		for i in range(self.cGroundHandler.getNumEntries()):
			self.cGroundHandler.sortEntries()
			entry = self.cGroundHandler.getEntry(i)
			object = entry.getIntoNode()
			self.groundCollideHandler(entry)
			# stop after first one
			break
		for i in range(self.cWallHandler.getNumEntries()):
			self.cWallHandler.sortEntries()
			entry = self.cWallHandler.getEntry(i)
			object = entry.getIntoNode()
			self.wallCollideHandler(entry)
			# stop after first one
			break
		return Task.cont
	
	def groundCollideHandler( self, colEntry ):
		# get Z position of collision
		newZ = colEntry.getSurfacePoint(render).getZ()
		# set position node of camera above collision point
		base.camera.setZ(newZ+GROUNDDISTANCE)
	
	def wallCollideHandler( self, colEntry ):
		# get position of collision
		collisionPos = colEntry.getSurfacePoint(render)
		# get position of camera
		cameraPos = base.camera.getPos(render)
		# distance from collisionpoint to camera
		distance = collisionPos - cameraPos
		# length of the distance
		distanceLength = distance.length()
		# if distance to collision point smaller then defined
		if distanceLength < MINDISTANCEWALL:
			# move camera backwand to be at correct distance
			base.camera.setPos( base.camera.getPos() - (distance * (MINDISTANCEWALL - distanceLength)))
	
	
 