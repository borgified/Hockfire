import cameraRotation, cameraMovement, cameraAddons



class Camera():
	def __init__( self ):
		# create a parent node for the camera
		self.globalCameraPositionNode = render.attachNewNode("globalCameraPosition") 
		self.globalCameraPositionNode.reparentTo( render )
		
		self.camMoveNode = self.globalCameraPositionNode.attachNewNode("camMoveNode")
		base.camera.reparentTo( self.camMoveNode )
		base.camera.setZ(self.camMoveNode, 2)
		
		# add a camera rotation controlled by the mouse
		self.cameraRotation = cameraRotation.CameraRotation()
		# add a camera movement controlled by the keyboard
		self.cameraMovement = cameraMovement.CameraMovement()
		# enable some keys (esc:quit, l:toggleWireframe, p:print camera pos, 
		# i:analyze the renderNode, o:change to default mouse controls)
		self.cameraAddons = cameraAddons.CameraAddons()
