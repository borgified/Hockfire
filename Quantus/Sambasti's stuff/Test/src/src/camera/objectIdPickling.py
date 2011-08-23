from pandac.PandaModules import BitMask32, CollisionNode, CollisionRay, GeomNode, CollisionTraverser, CollisionHandlerQueue, Vec3, VBase3D, Point3, NodePath


GROUND = 2
WALLS  = 3
PICKABLE = 1

# or all bits
def bitMaskOr( bitMaskList ):
  bitOr = BitMask32.bit(0)
  for bit in bitMaskList:
    bitOr = bitOr | BitMask32.bit(bit)
  return bitOr

# global variable
OBJECTIDCOUNTER = 0
OBJECTIDMAPPING = dict()

class objectIdPicklingClass:
  def __init__( self, bit=[PICKABLE] ):
    global OBJECTIDMAPPING
    #Since we are using collision detection to do picking, we set it up like
    #any other collision detection system with a traverser and a handler
    self.picker = CollisionTraverser()            #Make a traverser
    self.pq     = CollisionHandlerQueue()         #Make a handler
    #Make a collision node for our picker ray
    self.pickerNode = CollisionNode('mouseRay')
    #Attach that node to the camera since the ray will need to be positioned
    #relative to it
    self.pickerNP = base.camera.attachNewNode(self.pickerNode)
    #Everything to be picked will use bit 1. This way if we were doing other
    #collision we could seperate it
    self.pickerNode.setFromCollideMask(bitMaskOr(bit))
    self.pickerRay = CollisionRay()               #Make our ray
    self.pickerNode.addSolid(self.pickerRay)      #Add it to the collision node
    #Register the ray as something that can cause collisions
    self.picker.addCollider(self.pickerNP, self.pq)
    #self.picker.showCollisions(render)
  
  def mousePick( self ):
    #Check to see if we can access the mouse. We need it to do anything else
    if base.mouseWatcherNode.hasMouse():
      #get the mouse position
      mpos = base.mouseWatcherNode.getMouse()
      
      #Set the position of the ray based on the mouse position
      self.pickerRay.setFromLens(base.camNode, mpos.getX(), mpos.getY())
      
      #Do the actual collision pass (Do it only on the squares for
      #efficiency purposes)
      self.picker.traverse( render )
      if self.pq.getNumEntries() > 0:
        #if we have hit something, sort the hits so that the closest
        #is first, and highlight that node
        self.pq.sortEntries()
        pickedObj = self.pq.getEntry(0).getIntoNodePath()
        #print pickedObj
        pickedObjObjectId = pickedObj.findNetTag( 'objectId' )
        if pickedObj.hasNetTag( 'objectId' ):
          return pickedObj.getNetTag( 'objectId' )
        else:
          print "pickedObj.hasNetTag( 'objectId' ) failed"
          return None
      else:
        print "self.pq.getNumEntries() = %i" % self.pq.getNumEntries()
        return None
    else:
      print "base.mouseWatcherNode.hasMouse() failed"
      return None
  
  def getPickerRayDirection( self, mousePos=None ): #posX, posY ):
    ''' return the direction of the ray sent trought the mouse
    '''
    # the pickerRay cannot be changed anyway once it has been set in a frame (BUG?)
    if base.mouseWatcherNode.hasMouse():
      mpos = base.mouseWatcherNode.getMouse()
      #mousePos = (mpos.getX(), mpos.getY())
      self.pickerRay.setFromLens(base.camNode, mpos.getX(), mpos.getY())
      # make a copy of the ray
      direction = self.pickerRay.getDirection()
      mouseRayDirection = Point3(direction.getX(), direction.getY(), direction.getZ())
      # and normalize it
      mouseRayDirection.normalize()
      return mouseRayDirection
  
  def getObjectMousePick( self ):
    objectId = self.mousePick()
    if OBJECTIDMAPPING.has_key( objectId ):
      return OBJECTIDMAPPING[objectId]
    else:
      return None

objectIdPickling = objectIdPicklingClass()

class objectIdCreatorClass:
  def makeNodePickable( self, nodePath, bit ):
    global OBJECTIDCOUNTER
    nodePath.objectId = str(OBJECTIDCOUNTER)
    OBJECTIDCOUNTER += 1
    
    global OBJECTIDMAPPING
    OBJECTIDMAPPING[nodePath.objectId] = nodePath
    
    nodePath.setTag('objectId', nodePath.objectId )
    print "objectIdCreatorClass.makePickable : setting objectId %s to %s" % (nodePath.objectId, nodePath )
    nodePath.setCollideMask(bitMaskOr(bit))
  
  def makeColObjPickable( self, nodePath, collisionObject, bit ):
    global OBJECTIDCOUNTER
    nodePath.objectId = str(OBJECTIDCOUNTER)
    OBJECTIDCOUNTER += 1
    
    global OBJECTIDMAPPING
    OBJECTIDMAPPING[nodePath.objectId] = nodePath
    
    collisionObject.setTag('objectId', nodePath.objectId )
    print "objectIdCreatorClass.makePickable : setting objectId %s to %s" % (nodePath.objectId, nodePath )
    collisionObject.setCollideMask(bitMaskOr(bit))
  
  def destroy( self, objectId ):
    del OBJECTIDMAPPING[objectId]

objectIdCreator = objectIdCreatorClass()

class collidableObject( NodePath ):
  def __init__( self, bitmask, *args, **kwargs ):
    self.bitmask = bitmask
    NodePath.__init__( self, *args, **kwargs )
    # this creates a objectId for this node
    objectIdCreator.makeNodePickable( self, self.bitmask )
  def startDrag( self ):
    # this function is called when the object is picked up
    pass
  def stopDrag( self ):
    # this function is called when the object is dropped
    pass
  def whileDrag( self, mouseRay ):
    # this function is called every frame while the Node is dragged
    pass
  def attachNewCollidableNode( self, node, bitmask=None ):
    if bitmask is None:
      bitmask = self.bitmask
    # attach a new node which is colliable
    NodePath.attachNewNode( self, node )
    self.setTag('objectId', self.objectId )
    self.setCollideMask( bitMaskOr(bitmask) )

class dragDropObject( collidableObject ):
  oldParent = None
  def __init__( self, *args, **kwargs ):
    collidableObject.__init__( self, *args, **kwargs )
  
  def stopDrag( self ):
    # this function is called when the object is dropped
    # reparent to old parent
    if self.oldParent is not None:
      self.wrtReparentTo( self.oldParent )

  def startDrag( self ):
    # this function is called when the object is picked up
    # get distance of the object to the camera (required for the task)
    self.pickedObjectDistance = self.getPos( base.camera ).length()
    # store old parent of object
    self.oldParent = self.getParent()
    # reparent the object to the camera, so it moves with it
    self.wrtReparentTo( base.camera )

  def whileDrag( self, mouseRay ):
    # this function is called every frame while the Node is dragged
    # move the object to the position the mouseray points at
    self.setPos( base.camera, mouseRay * self.pickedObjectDistance )