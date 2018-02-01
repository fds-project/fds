import os
import json
import threading
import time


#  empty logger template
class logger:
    def log(self, t, x): print("FileWatcher", t, x)


class FileWatcher:
    # watcher thread, calls to callback (async)
    thread = None

    # start?
    use_file_watcher = True

    # async? (only option atm)
    async = True

    # input file directory
    directory = 'input/'

    # seconds between cycle, timer starts after last callback
    watch_seconds = 5

    # should be passed or else template will be used (nulled out)
    logger = None

    # use json validation
    validate = False

    # go?
    go = True

    def __init__(self):
        if not self.logger:
            #  hopefully this does not happen :)
            self.logger = logger()

        if not os.path.exists(self.directory):
            self.logger.log("[FileWatcher] Creating input file directory", 3)
            os.mkdir(self.directory)

        if self.async:
            self.thread = WatcherThread(self)
            self.logger.log("[FileWatcher] Starting FileWatcher Thread for Async file creation monitoring", 3)
            self.thread.start()
            self.logger.log("[FileWatcher] Started FileWatcher Thread", 3)

    # This callback function will be called whenever a file is created in the input folder
    # Do not use this for slow tasks but save the paths somewhere for later
    # Function is ASYNC so make minimal use of the logger

    def callback(self, file):
        self.logger.log("[FileWatcher] The callback function is not implemented, new data will not be read", 2)
        return

    # Same for the Error function
    def error(self, file):
        self.logger.log("[FileWatcher] The error function is not implemented, errors will not be reported", 2)
        return

    # Also runs async
    def isOk(self):
        # todo?, advanced logic!
        return self.go

    def stop(self):
        self.go = False
        self.logger.log("[FileWatcher] Closing log thread, waiting %d seconds to make sure it's shut down" %
                        (self.watch_seconds * 2), 3)
        time.sleep(self.watch_seconds * 2)
        return not self.thread.is_alive()


class WatcherThread(threading.Thread):
    parent = None

    def __init__(self, parent):
        threading.Thread.__init__(self)

        self.parent = parent

    def run(self):
        while self.parent.isOk():
            dir = self.parent.directory

            files = [os.path.join(dir, f) for f in os.listdir(dir) if os.path.isfile(os.path.join(dir, f))]
            
            for file in files:
                if self.parent.validate:
                    try:
                        with open(file, 'r') as f:
                            json.loads(f.read())
                        # no exception, should be valid json ## callback ##
                        self.parent.callback(file)
                    except:
                        #  invalid json, skip this one
                        self.parent.error(file)
                        continue
                else:
                    self.parent.callback(file)

            time.sleep(self.parent.watch_seconds)


if __name__ == "__main__":
    #  test class for file watcher
    #  keeps list of seen files locally to ensure threading speed
    class TestWatcher(FileWatcher):
        seenfiles = []

        def __init__(self):
            FileWatcher.__init__(self)

        def callback(self, file):
            if file not in self.seenfiles:
                self.seenfiles.append(file)
                print("New File: %s" % file)

    baaa = TestWatcher()
    x = input()
    print("Successfully stopped filewatcher system" if baaa.stop() else "Error stopping system!")
