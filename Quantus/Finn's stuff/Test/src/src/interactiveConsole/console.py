# -----
# customConsoleClass
# -----
# by Reto Spoerri
# rspoerri AT nouser.org
# http://www.nouser.org
# -----
# wraps the interactiveConsole
# -----


from shared import *
from completer import completePython

import sys, inspect

from code import InteractiveConsole

class FileCacher:
  "Cache the stdout text so we can analyze it before returning it"
  def __init__(self):
    self.reset()
  def reset(self):
    self.out = []
  def write(self,line):
    self.out.append(line)
  def flush(self):
    output = '\n'.join(self.out).rstrip()
    self.reset()
    return output
class customConsoleClass( InteractiveConsole ):
  inputColor  = (1.0,0.8,1.0,1.0)
  outputColor = (0.8,1.0,1.0,1.0)
  def __init__( self, localsEnv=globals() ):
    InteractiveConsole.__init__( self, localsEnv )
    print "customConsoleClass", localsEnv
    self.consoleLocals = localsEnv
    
    # catch the output of the interactive interpreter
    self.stdout = sys.stdout
    self.stderr = sys.stderr
    self.cache = FileCacher()
    
    self.help()
  
  def help( self ):
    text = " ------ InteractiveConsole ------ \n"
    if PYTHON_PRE is None:
      text += """- direct entry enabled"""
    else:
      text += """- use '%s' in front of a line to send it to the interactiveConsole component
- example: %sfor i in xrange(10):  # no spaces between the ! and the 'for'
- example: %s    print i
- example: %s <enter>\n"""  % (PYTHON_PRE,PYTHON_PRE,PYTHON_PRE,PYTHON_PRE)
    text += """- BUGS   : do not try to call something like 'while True:'
    you will not be able to break it, you must at least include 'Task.step()'
TAB      : autocomplete commands
F1       : help"""
    return text
  
  def get_output( self ):
    sys.stdout = self.cache
    sys.stderr = self.cache
  def return_output( self ):
    sys.stdout = self.stdout
    sys.stderr = self.stderr
  
  def push( self, input ):
    output = list()
    output.append( ["%s" % input, '>>> ', self.inputColor] )
    
    # execute on interactiveConsole console
    self.get_output()
    InteractiveConsole.push( self, input )
    self.return_output()
    
    resultText = self.cache.flush()
    
    if len(resultText) > 0:
      output.append( ["%s" % resultText, '> ', self.outputColor] )
    
    return output
  
  def autocomplete( self, pythonText, currentCursorPos ):
    newText = pythonText
    printText = None
    
    pythonTestSplit = pythonText.split(' ')
    env = self.consoleLocals
    term = completePython( env, pythonText )
    
    # if the entered name is uniq, use autocomplete
    if len(term) == 1:
      newTextList = pythonTestSplit[0:-1]
      newTextList.append( term[0] )
      newText = ' '.join(newTextList)
    # output the list of available names
    elif len(term) > 1:
      printText = str(term)
    
    return newText, printText
  
  def autohelp( self, pythonText, currentCursorPos ):
    # read the docstring
    out = self.push( "%s.__doc__" % pythonText )
    print "__doc__ of %s: %s" % (pythonText, out)
    # if no docstring found
    if len(out) == 1:
      # read the first five lines of the sourcecode
      try:
        self.push( "import inspect" )
        out = self.push( "inspect.getsourcelines( %s )[0][0:6]" % pythonText )
      except:
        out = [],[['no helptext found'],],
    # return the help text
    lines = out[1][0]
    exec( "helpText = ''.join(%s)" % lines )
    helpText = "--- help for %s ---\n" % (pythonText) + helpText
    return helpText
    