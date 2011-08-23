import sys, re, string

from code import InteractiveConsole
from direct.gui.DirectGui import DirectFrame, DirectEntry, DirectLabel, DGG
from direct.showbase import DirectObject
from pandac.PandaModules import TextNode
from direct.gui.OnscreenText import OnscreenText


#import InteractiveConsole

if sys.platform == 'win32':
  import msvcrt
elif sys.platform == 'linux2' or sys.platform == 'darwin':
  import termios, fcntl, sys, os

# vertical space from top
V_SPACE = 0.0
# vertical size of console
V_SIZE = 1.5
# size of the text
SCALE = 0.05
# when changing scale change this as well
LINELENGTH = 80

# do not change unless you change the size of the frames
NUMLINES = int(V_SIZE/SCALE - 3)

GUI = 1
CONSOLE = 2


# ---- character reading from console : BEGIN ----

class _Getch:  """Gets a single character from standard input.  Does not echo to thescreen."""  def __init__(self):    try:      self.impl = _GetchWindows()    except ImportError:      try:        self.impl = _GetchMacCarbon()      except AttributeError:        self.impl = _GetchUnix()  def __call__(self): return self.impl()
    
class _GetchUnix:  def __init__(self):    import tty, sys, termios # import termios now or else you'll get the Unix version on the Mac  def __call__(self):    import sys, tty, termios    fd = sys.stdin.fileno()    old_settings = termios.tcgetattr(fd)    try:      tty.setraw(sys.stdin.fileno())      ch = sys.stdin.read(1)    finally:      termios.tcsetattr(fd, termios.TCSADRAIN, old_settings)    return chclass _GetchWindows:  def __init__(self):    import msvcrt  def __call__(self):    import msvcrt    return msvcrt.getch()
class _GetchMacCarbon:  """  A function which returns the current ASCII key that is down;  if no ASCII key is down, the null string is returned.  The  page http://www.mactech.com/macintosh-c/chap02-1.html was  very helpful in figuring out how to do this.  """  def __init__(self):    import Carbon    Carbon.Evt #see if it has this (in Unix, it doesn't)  def __call__(self):    import Carbon    if Carbon.Evt.EventAvail(0x0008)[0]==0: # 0x0008 is the keyDownMask      return ''    else:      #      # The event contains the following info:      # (what,msg,when,where,mod)=Carbon.Evt.GetNextEvent(0x0008)[1]      #      # The message (msg) contains the ASCII char which is      # extracted with the 0x000000FF charCodeMask; this      # number is converted to an ASCII character with chr() and      # returned      #      (what,msg,when,where,mod)=Carbon.Evt.GetNextEvent(0x0008)[1]      return chr(msg & 0x000000FF)

# ---- character reading from console - END ----





class pandaInteractiveConsole( InteractiveConsole, DirectObject.DirectObject ):
  def __init__( self, locals, interfaces=GUI|CONSOLE ):
    print "I: pandaInteractiveConsole.__init__"
    
    InteractiveConsole.__init__( self, locals )
   
    self.guiEnabled = (interfaces & GUI)
    if self.guiEnabled:
      print "I:  - GUI enabled"
      
      top = 1/SCALE - (V_SPACE/SCALE)
      bottom = 1/SCALE - (V_SPACE/SCALE) - (V_SIZE/SCALE)
      # panda3d interface
      self.consoleFrame = DirectFrame( relief = DGG.GROOVE
        , frameColor = (200, 200, 200, 0.85), scale=SCALE
        , frameSize = (-1/SCALE, 1/SCALE, top, bottom))
      self.accept( "f1", self.toggleConsole )
      # text entry line
      self.consoleEntry = DirectEntry( self.consoleFrame, text = "", command=self.setText, width = 1/SCALE*2-2
        , pos =(-1/SCALE+1,0,bottom+1), initialText="", numLines = 1, focus=1)
      # output lines
      self.consoleOutputList = list()
      for i in xrange( NUMLINES ):
        label = OnscreenText( parent = self.consoleFrame, text = "", pos = (-1/SCALE+1, bottom+1+NUMLINES-i) #-1-i)
          , align=TextNode.ALeft, mayChange=1, scale=1.0)
        self.consoleOutputList.append( label )
        self.toggleConsole()
        
    self.consoleEnabled = (interfaces & CONSOLE)
    self.stdout = sys.stdout
    if self.consoleEnabled:
      print "I:  - Console enabled"
      # function to read the keyboard inputs
      self.readKey = _Getch()
      
      # redirect input and output
      self.stdout = sys.stdout
      sys.stdout = self
      if sys.platform == 'linux2' or sys.platform == 'darwin':
        self.setConsoleNonBlocking( self.stdout )
    
    self.write( "D: myInteractiveConsole.__init__\n" )
    # buffer of current text
    self.linebuffer = ''
    self.linebufferPos = 0
  
  def setConsoleBlocking( self, fd_in ):
    fd = fd_in.fileno()
    fl = fcntl.fcntl(fd, fcntl.F_GETFL)
    try:    	fcntl.fcntl(fd, fcntl.F_SETFL, fl | os.FNDELAY)
    except:
      pass  
  def setConsoleNonBlocking( self, fd_in ):
    fd = fd_in.fileno()
    fl = fcntl.fcntl(fd, fcntl.F_GETFL)
    try:
      fcntl.fcntl(fd, fcntl.F_SETFL, fl | os.O_NONBLOCK)
    except:
      self.write( "D: console init failed\n" )
      self.setConsoleBlocking( fd )
    
    '''
    # set read non blocking
    fd = sys.stdin.fileno()
    self.oldterm = termios.tcgetattr(fd)
    newattr = self.oldterm
    self.oldflags = fcntl.fcntl(fd, fcntl.F_GETFL)
    newattr[3] = newattr[3] & ~termios.ICANON & ~termios.ECHO
    termios.tcsetattr(fd, termios.TCSANOW, newattr)
    fcntl.fcntl(fd, fcntl.F_SETFL, self.oldflags | os.O_NONBLOCK)
    '''
  
  def __del__( self ):
    if self.consoleEnabled:
      sys.stdout = self.stdout
      if sys.platform == 'linux2' or sys.platform == 'darwin':
        self.write( "setting old terminal settings\n" )
        fd = sys.stdin.fileno()
        termios.tcsetattr(fd, termios.TCSAFLUSH, self.oldterm)
        fcntl.fcntl(fd, fcntl.F_SETFL, self.oldflags)
  
  def toggleConsole( self ):
    self.consoleFrame.toggleVis()
    self.consoleEntry['focus'] != self.consoleFrame.isHidden
  
  def setText(self, textEntered):
    self.write( ">%s" % textEntered )
    output = self.push( textEntered )
    # clear line
    self.consoleEntry.enterText('')
    self.consoleEntry['focus'] = 1
  
  def writeP3dConsole( self, printString ):
    # remove carriage returns (causes disort in output)
    printString = printString.strip()
    # remove not printable characters (which can be input by console input)
    printString = re.sub( r"[^%s]" % string.printable, "", printString )
    # only write lines which contain something
    if printString.strip() != '':
      while len(printString) > LINELENGTH:
        writeString = printString[0:LINELENGTH]
        printString = printString[LINELENGTH:]
        # output on panda3d interface
        for i in xrange( NUMLINES-1 ):
          self.consoleOutputList[i].setText( self.consoleOutputList[i+1].getText() )
        self.consoleOutputList[NUMLINES-1].setText( writeString )
      # output on panda3d interface
      for i in xrange( NUMLINES-1 ):
        self.consoleOutputList[i].setText( self.consoleOutputList[i+1].getText() )
      self.consoleOutputList[NUMLINES-1].setText( printString )
  
  def write( self, string ):
    # write a text the console
    try:
      if self.consoleEnabled:
        # output on console
        self.stdout.write( string )
      # output on panda3d interface
      if self.guiEnabled:
        self.writeP3dConsole( string )
    except IOError:
      # this can happen on the linux server, dont know why
      pass
    except UnicodeEncodeError:
      # dont know how to handle this currently (maybe needs manual conversion)
      pass

  def refreshLine( self ):
    # clear complete line
    for i in xrange(len(self.linebuffer)):
      self.write("\b")
    # write it again
    self.write( self.linebuffer+" " )
    self.write("\b")
 
  def read( self ):
    if self.consoleEnabled:
      # get keypress
      char = self.readKey()
      while char is not None:
        # clear last character on backspace
        if ord(char) == 8 or ord(char) == 127:
          self.write(char)
          self.linebuffer = self.linebuffer[0:self.linebufferPos-1] + self.linebuffer[self.linebufferPos:len(self.linebuffer)]
          self.linebufferPos = max(0, self.linebufferPos - 1)
          self.refreshLine()
        # execute command on carriage return
        elif ord(char) == 13 or ord(char) == 10: # 13 on windows / 10 on linux ???
          self.write( "\n> exec '%s'" % self.linebuffer )
          print
          output = self.push( self.linebuffer )
          self.linebuffer = ''
          self.linebufferPos = 0
        # move cursor to edit commandline
        elif ord(char) == 224 or ord(char) == 1: # ord(char) == 27
          if sys.platform == 'linux2' or sys.platform == 'darwin':
            cmdKey = self.readKey()
          cmdKey = self.readKey()
          if ord(cmdKey) == 75 or ord(cmdKey) == 68:
            self.linebufferPos = max( 0, min( len(self.linebuffer), self.linebufferPos - 1 ) )
          elif ord(cmdKey) == 77 or ord(cmdKey) == 67:
            self.linebufferPos = max( 0, min( len(self.linebuffer), self.linebufferPos + 1 ) )
        # all other keypresses are added to the command line
        else:
          self.linebuffer = self.linebuffer[0:self.linebufferPos] + char + self.linebuffer[self.linebufferPos:len(self.linebuffer)]
          if self.linebufferPos+1 != len(self.linebuffer):
            diffLen = len(self.linebuffer) - self.linebufferPos+1
            self.write(char)
            self.refreshLine()
          else:
            self.write(char)
          self.linebufferPos += 1
        # get keypress
        char = self.readKey() 