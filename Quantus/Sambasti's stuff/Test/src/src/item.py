from pandac.PandaModules import Vec3
from pandac.PandaModules import NodePath

from objectIdPickling import objectIdPicklingClass, objectIdClass

class itemClass(objectIdClass):
  def __init__( self, ground ):
    objectIdClass.__init__( self )
    self.ground = ground
    # create collision object
    pass
    # create position node
    self.positionNp = NodePath( 'positionNp%s' % self.objectId )
    #self.positionNp.reparentTo( render )
    # load model
    self.modelNp = loader.loadModelCopy( 'data/models/box.egg' )
    self.modelNp.reparentTo( self.positionNp )
    
    self.setPos( Vec3(0,0,0) )
    
    self.create3dObject()
 
  def destroy( self ):
    objectIdClass.destroy( self )
  
  def create3dObject( self ):
    self.reparentTo( render )
    #self.positionNp.show()
    self.makePickable( self.modelNp )
  
  def setPos( self, position ):
    xPos, yPos, zPos = position.getX(), position.getY(), position.getZ()
    zGround = self.ground.get_elevation( [xPos, yPos] )
    zPos = zGround
    #if zPos < zGround:
    #  zPos = zGround
    return self.positionNp.setPos( Vec3(xPos, yPos, zPos) )
  
  def getPos( self, relativeTo=None ):
    return self.positionNp.getPos( relativeTo )
  
  def setHpr( self, hpr ):
    return self.positionNp.setHpr( hpr )
  
  def reparentTo( self, object ):
    return self.positionNp.reparentTo( object )
  
  def wrtReparentTo( self, object ):
    return self.positionNp.wrtReparentTo( object )
  
  def putOnGround( self, xyPos ):
    return self.ground.get_elevation( xyPos )
    
  
  def destroy3dObject( self ):
    self.positionNp.detachNode()
    #self.positionNp.hide()
    # destroy collision object
    # destroy shape
    #pass
  
  def create2dObject( self ):
    pass
  
  def destroy2dObject( self ):
    pass