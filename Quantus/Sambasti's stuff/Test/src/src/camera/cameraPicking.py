from pandac.PandaModules import Vec3, Point3, NodePath
from direct.showbase.DirectObject import DirectObject
from direct.task import Task

from objectIdPickling import objectIdPickling

class cameraPickingClass( DirectObject ):
  enabled = False
  def __init__( self ):
    self.keybindings  = { "mouse1"     : [self.mousePick, 1] 
                        , "mouse1-up"  : [self.mousePick, 0] }
    self.mouseSetup()
    self.enable()
  
  def enable( self ):
    self.enabled = True
    # set keyboard mappings
    for mapping, [binding, setting] in self.keybindings.items():
      if setting is not None:
        self.accept( mapping, binding, [setting] )
      else:
        self.accept( mapping, binding )
  
  def disable( self ):
    self.enabled = False
    self.ignoreAll()
  
  def mouseSetup( self ):
    # create a node in front of the camera
    self.cameraFrontNode = NodePath( 'cameraFrontNode' )
    self.cameraFrontNode.reparentTo( base.camera )
    self.cameraFrontNode.setPos( Vec3(0,3,0) )
    self.pickedObject = None
    self.pickedObjectDistance = 0
  
  def mousePick( self, keyDown ):
    # pick up item
    if keyDown:
      pickedObject = objectIdPickling.getObjectMousePick()
      if pickedObject:
        self.startDrag( pickedObject )
    # drop item
    else: # key release
      if self.pickedObject:
        self.stopDrag( self.pickedObject )
  
  def startDrag( self, dragNode ):
    # save the object we drag&drop
    self.pickedObject = dragNode
    self.pickedObject.startDrag()
     # start the object move task (required to have the object moving with a free mouse)
    taskMgr.add( self.dragTask, 'mouseDragTask' )
  
  def stopDrag( self, dragNode ):
    self.pickedObject.stopDrag()
    # we dont drag&drop anything now
    self.pickedObject = None
    taskMgr.remove( 'mouseDragTask' )
  
  def dragTask( self, task ):
    # get a ray where the mouse points at
    newMouseRay = objectIdPickling.getPickerRayDirection()
    self.pickedObject.whileDrag( newMouseRay )
    return Task.cont

if __name__ == '__main__':
  cameraPicking = cameraPickingClass()
  