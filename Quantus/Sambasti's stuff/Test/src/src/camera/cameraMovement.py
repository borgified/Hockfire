from pandac.PandaModules import Vec3
from direct.showbase.DirectObject import DirectObject
from direct.task import Task

# movement speed of the camera [forward/reverse, left/right, up/down]
MOVEMENTSPEED = [15.0, 10.0, 5.0]

# directions of movement
MOVE_RIGHT   = ( 1, 0, 0)
MOVE_LEFT    = (-1, 0, 0)
MOVE_FORWARD = ( 0, 1, 0)
MOVE_REVERSE = ( 0,-1, 0)
MOVE_UP      = ( 0, 0, 1)
MOVE_DOWN    = ( 0, 0,-1)

class cameraMovementClass( DirectObject ):
  actions = dict()
  enabled = False
  def __init__( self ):
    # setup key bindings
    keybindings = { "w"         : [self.keypress, [MOVE_FORWARD, 1]]
                  , "w-up"      : [self.keypress, [MOVE_FORWARD, 0]]
                  , "s"         : [self.keypress, [MOVE_REVERSE, 1]]
                  , "s-up"      : [self.keypress, [MOVE_REVERSE, 0]]
                  , "a"         : [self.keypress, [MOVE_LEFT, 1]]
                  , "a-up"      : [self.keypress, [MOVE_LEFT, 0]]
                  , "d"         : [self.keypress, [MOVE_RIGHT, 1]]
                  , "d-up"      : [self.keypress, [MOVE_RIGHT, 0]]
                  , "q"         : [self.keypress, [MOVE_UP, 1]]
                  , "q-up"      : [self.keypress, [MOVE_UP, 0]]
                  , "e"         : [self.keypress, [MOVE_DOWN, 1]]
                  , "e-up"      : [self.keypress, [MOVE_DOWN, 0]] }
    
    # set keyboard mappings
    for mapping, [binding, setting] in keybindings.items():
      if setting is not None:
        self.accept( mapping, binding, setting )
      else:
        self.accept( mapping, binding )
    
    # create a parent node for the camera
    self.globalCameraPositionNode = render.attachNewNode("globalCameraPosition") 
    self.globalCameraPositionNode.reparentTo( render )
    base.camera.reparentTo( self.globalCameraPositionNode )
    
    self.enable()
  
  def enable( self ):
    self.enabled = True
    taskMgr.add(self.movementTask, 'movementTask')
  
  def disable( self ):
    self.enabled = False
    taskMgr.remove('movementTask')
  
  def movementTask( self, task ):
    for movement, enabled in self.actions.items():
      if enabled:
        movement = Vec3( MOVEMENTSPEED[0] * movement[0], MOVEMENTSPEED[1] * movement[1], MOVEMENTSPEED[2] * movement[2] )
        base.camera.setPos( base.camera, movement * globalClock.getDt() )
    return Task.cont
  
  def keypress( self, action, enabled ):
    self.actions[action] = enabled







