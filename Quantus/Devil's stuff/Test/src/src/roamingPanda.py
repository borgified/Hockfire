import random, time
from direct.actor import Actor 
from direct.task.Task import Task
from pandac.PandaModules import Vec3, NodePath
from direct.interval.IntervalGlobal import Sequence, LerpFunc, LerpColorInterval, Parallel

MOVEMENTSPEED = 250.0

UPDATETIME = 3.0

def limit( in_val, in_min, in_max ):
  # make sure min and max are min and max
  t_max = max( in_min, in_max )
  t_min = min( in_min, in_max )
  # return in_val, limited, by in_min and in_max
  return max( min( in_val, in_max ), in_min )

#from src.camera.objectIdPickling import dragDropObject

'''
class dragDropPandaClass( roamingPandaClass, dragDropObject ):
  def __init__( self, *args, **kwargs ):
    roamingPandaClass.__init__( self, *args, **kwargs )
    dragDropObject.__init__( self )
    #self.heightfield, 1000.0, MAPSIZE/2.0, [objectIdPickling.PICKABLE]
  
  def startDrag( self ):
    dragDropObject.startDrag( self )
    self.pandaActor.setPos( Vec3(0,0,0) )
    self.setPos( Vec3(0,0,0) )
  
  def stopDrag( self ):
    dragDropObject.stopDrag( self )
    pass
    #self.getHeight( [base.camera.getX(), base.camera.getY()] )
'''

class roamingPandaClass( NodePath ):
  def __init__( self, ground, distance, maxPos, *args, **kwargs ):
    NodePath.__init__( self, "roamingPandaNode", *args, **kwargs )
    self.ground = ground
    self.maxPos = maxPos
    
    #Load the panda actor, and loop its animation
    self.pandaActor = Actor.Actor("data/models/panda/panda-model",{"walk":"data/models/panda/panda-walk4"})
    self.pandaActor.setScale(0.01,0.01,0.01)
    self.pandaActor.reparentTo(self ) #.globalPandaNode)
    
    self.pandaActor.loop("walk")
    #pandaActor.setPos( 
    taskMgr.doMethodLater(UPDATETIME, self.movementTask, 'pandaMovementTask')
    
    # randomize the startposition of the panda
    self.rotate( 1.0, random.randint( -180, 180 ) )
    self.moveForward( distance/6.0*3.0 )
    self.rotate( 1.0, random.randint( -180, 180 ) )
    self.moveForward( distance/6.0*2.0 )
    self.rotate( 1.0, random.randint( -180, 180 ) )
    self.moveForward( distance/6.0*1.0 )
  
  def movementTask( self, task ):
    # show and hide depending on distance to camera
    if (base.camera.getPos( render ) - self.getPos()).length() > 100.0:
      self.pandaActor.hide()
    else:
      self.pandaActor.show()
    
    try:
      # calculate time passed / we cant use task.time in a doMethodLater
      deltaT = time.time() - self.oldTaskTime
      # save current time
      self.oldTaskTime = time.time()
    except:
      # save current time
      self.oldTaskTime = time.time()
      deltaT = 0.0
    
    # get future rotation and position
    rot = self.getRotation( UPDATETIME, random.randint( -10, 10) )
    pos = self.getForwardPos( UPDATETIME * (random.random()/2.0+1.0) )
    posZ = self.getHeight( (pos.getX(), pos.getY()) )
    pos.setZ( posZ )
    
    # create movement and rotation intervals
    myInterval1=self.posInterval(UPDATETIME*1.25,pos)
    myInterval2=self.hprInterval(UPDATETIME*1.25,rot)
#    myInterval1=self.globalPandaNode.posInterval(UPDATETIME*1.25,pos)
#    myInterval2=self.globalPandaNode.hprInterval(UPDATETIME*1.25,rot)
    myParallel=Parallel(myInterval1,myInterval2) 
    myParallel.start()
    
    return Task.again
  
  def rotate( self, deltaT, rotation ):
    rot = self.getRotation( deltaT, rotation )
    self.pandaActor.setHpr( rot )
  
  def getRotation( self, deltaT, rotation ):
    angledegrees = deltaT * rotation
    # get current rotation
    currentHpr = self.pandaActor.getHpr()
    return Vec3( currentHpr[0] - angledegrees, currentHpr[1], 0)
  
  def moveForward( self, deltaT ):
    pos = self.getForwardPos( deltaT )
    self.setPos( pos )
#    self.globalPandaNode.setPos( pos )

  def getForwardPos( self, deltaT ):
    deltaY = deltaT * MOVEMENTSPEED
    # move camera
    self.pandaActor.setY( self.pandaActor, -deltaY )
    # get global position of camera
    globalPos = self.pandaActor.getPos( render )
    # limit the position
    globalPos.setX( limit(globalPos.getX(), -self.maxPos, self.maxPos) )
    globalPos.setY( limit(globalPos.getY(), -self.maxPos, self.maxPos) )
    # reset position of panda
    self.pandaActor.setPos( Vec3( 0,0,0 ) )
    # set position to camera
    return globalPos  

  def setHeight( self ):
    x,y = self.pandaActor.getX( render ), self.pandaActor.getY( render )
    height = self.ground.get_elevation([x, y]) + 0.5
    self.setZ( height )
#    self.globalPandaNode.setZ( height )
  
  def getHeight( self, position ):
    return self.ground.get_elevation(position) + 0.5
  