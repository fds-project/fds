import os
import re
import time
import datetime
import base64
from Crypto.Cipher import AES
import json
import hashlib


class logger:
    #  directory for logs, default: $project/logs/
    directory = 'logs/'

    #  days to keep logs, older will be automatically deleted, set to None to disable cleanup
    log_max_days = 7

    #  logfile name, make sure to use _timestamp.extension in the name (cleanup process)
    name = "log_%s.txt" % time.time()

    #  log for x hours
    log_hours = 24

    #  last log time
    __lastlog = 0

    #  setup some things
    cleanup_match = re.compile('_(\d+)\.\w+$')

    #  default log level, set to 0 to disable logging without levels
    default_log_level = 3

    #  the minimal log level, <= 0 will be ignored (useful for modules that are disabled)
    #  set to -1 to prevent the logger from skipping input accidentally
    min_log_level = -1

    #  default log levels
    log_levels = {0: 'None', 1: 'Error', 2: 'Warning', 3: 'Verbose'}

    #  Log level as full uppercase, set to False to use regular casing
    upper = True

    #  internal use, switches logging to file on if directory structure is correct
    __use_logfile = False

    handle = None

    #  log terminal color setup
    colors = {0: '\x1b[0;30;42m', 1: '\x1b[0;31;40m', 2: '\x1b[0;33;40m', 3: '\x1b[0;34;40m', 99: '\x1b[0m'}

    #  can break some things (unsafe but cool)
    use_colors = True

    def cleanup(self):
        #  todo, cleanup old files based upon timestamps
        pass

    def __init__(self):
        self.__setup_dirstructures()
        if self.use_colors:
            print("[LOGGER] Console colors are enabled, "
                  "if things look weird disable color mode using the use_colors parameter")

    # create new log file, calling this externally would close current log and start new one
    def newlog(self):
        if self.__use_logfile:
            self.handle = None
            self.__lastlog = time.time()
            self.handle = open(self.directory + self.name, 'w')
            self.handle.write("Starting logfile at %s\n\n" % self.flog())

    # write log output to file (after some checks)
    def write(self, text):
        #  exits if log system is not started, for safety because method is public
        if self.__use_logfile:
            #  check for trailing newlines
            text = text + '\n' if not text.endswith('\n') else text

            #  not started log, start new one
            if self.__lastlog is 0:
                self.newlog()
                self.handle.write(text)
            # last log is too old, create new one
            elif self.__lastlog >= time.time() + (self.log_hours * 60 * 60):
                self.end()
                self.newlog()
                self.handle.write(text)
            else:
                self.handle.write(text)

    # private, creates log dir
    def __setup_dirstructures(self):
        if not os.path.exists(self.directory):
            self.log("Creating log directory", 3)
            try:
                os.mkdir(self.directory)
            except:
                self.log("Error creating log directory, logging to file will be disabled")
                return
        # all ready, go
        self.__use_logfile = True

    #  returns more fancy datetime output :)
    def flog(self):
        return str(datetime.datetime.now()).split('.')[0]

    #  public function, should be called whenever something is logged.. either good or bad
    def log(self, text, level=default_log_level):
        #  log level will be ignored :)
        if level < self.min_log_level:
            return

        ll = self.log_levels[level] if level in self.log_levels else self.log_levels[0]
        if self.upper: ll = ll.upper()

        #  construct the log line
        log_txt = "[%s] %s> %s" % (ll, self.flog(), text)

        #  write to file
        if self.__use_logfile:
            self.write(log_txt)

        # print to console
        if self.use_colors and level in self.colors:
            print(self.colors[level] + text + self.colors[99])
        else:
            print(text)

    def end(self):
        if self.handle:
            try:
                self.handle.write("\nEnding logfile at %s" % self.flog())
                self.handle.close()

            except:
                pass


class AESCipher(object):
    bs = 32

    def __init__(self, key, iv):
        self.key = key
        self.iv = base64.b64decode(iv)

    def encrypt(self, raw):
        pad = lambda s: s + (self.bs - len(s) % self.bs) * chr(self.bs - len(s) % self.bs)
        raw = pad(raw)
        iv = self.iv
        cipher = AES.new(self.key, AES.MODE_CFB, iv)
        return base64.b64encode(cipher.encrypt(raw.encode('utf-8')))

    def decrypt(self, enc):
        unpad = lambda s: s[:-ord(s[len(s) - 1:])]
        enc = base64.b64decode(enc)
        iv = self.iv
        cipher = AES.new(self.key, AES.MODE_CFB, iv)
        return unpad(cipher.decrypt(enc)).decode('utf-8')


# class for generating the profiler HTML result
class HTMLEngine:
    #  the class and id to use for the HTML table (default: bootstrap table)
    tableclass = 'table'

    tableid = 'warnings'

    bubble_types = {1: 'badge-danger', 2: 'badge-warning', 3: 'badge-primary', 4: 'badge-success'}
    risk_levels = {1: 'High risk', 2: 'Medium risk', 3: 'Low risk', 4: 'No risk'}

    #  generate a HTML table, has_headers uses first row as header row
    def table(self, data, has_headers=True):
        html = '<table class="%s" id="%s">' % (self.tableclass, self.tableid)
        if has_headers:
            headers = data.pop(0)
            html += '<thead><tr>'
            for tab in headers:
                html += '<th>%s</th>' % tab
            html += '</tr></thead>'
        html += '<tbody>'
        for datarow in data:
            html += '<tr>'
            for datafield in datarow:
                html += '<td>%s</td>' % datafield
            html += '</tr>'
        html += '</tbody></table>'
        return html

    #  generate a bubble using the bootstrap badge component
    def bubble(self, text, colorType=3):
        if colorType not in self.bubble_types:
            colortype = 3
        return '<span class="badge %s">%s</span>' % (self.bubble_types[colorType], text)

    def jsondata(self, riskText, riskDataType, warnings):
        data = {'profile': {'riskText': riskText, 'riskType': riskDataType, 'warnings': warnings}}
        return '<div style="display: none;">%s</div>' % json.dumps(data)

    def generateProfile(self, textData, warnings=[], riskType=3):
        profileBody = """
        %s
        <br/>
        <strong>risk detection: %s</strong>
        <hr />
        <h4>Potential warnings:</h4>
        <i>The following items where detected</i>
        <br />
        %s
        %s
        """ % (textData, self.bubble(self.risk_levels[riskType], riskType), self.table(warnings),
               self.jsondata(self.risk_levels[riskType], riskType, warnings))
        return profileBody


if __name__ == "__main__":
    # Code for testing logger

    x = logger()
    x.log("Default log level, should be %s" % x.log_levels[x.default_log_level])
    x.log("Verbose log entry", 3)
    x.log("Warning log entry", 2)
    time.sleep(3)
    x.log("Error log entry", 1)
    x.log("Should not be listed", 0)
    x.end()

    # Test code for AESCipher
    logger = logger()
    key = '`?.F(fHbN6XK|j!t'
    cipher = AESCipher(key, logger)

    cipher.save("{test: 1}", "C:\\Users\\wesse\\Desktop\\encrypted.json")
    t = cipher.load("C:\\Users\\wesse\\Desktop\\encrypted.json")
    print(t)

    # plaintext = '542#1504891440039'
    # encrypted = cipher.encrypt(plaintext)
    # print('Encrypted: %s' % encrypted)
    # ciphertext = '5bgJqIqFuT8ACuvT1dz2Bj5kx9ZAIkODHWRzuLlfYV0='
    # assert encrypted == ciphertext

    # decrypted = cipher.decrypt(encrypted)
    # print('Decrypted: %s' % decrypted)

    # assert decrypted == plaintext
