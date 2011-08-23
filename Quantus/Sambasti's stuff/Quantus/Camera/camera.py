import cameraRotation, cameraMovement, cameraAddons



class Camera():
	def __init__( self ):
		# add a camera rotation controlled by the mouse
		self.cameraRotation = cameraRotation.CameraRotation()
		# add a camera movement controlled by the keyboard
		self.cameraMovement = cameraMovement.CameraMovement()
		# enable some keys (esc:quit, l:toggleWireframe, p:print camera pos, 
		# i:analyze the renderNode, o:change to default mouse controls)
		self.cameraAddons = cameraAddons.CameraAddons()
