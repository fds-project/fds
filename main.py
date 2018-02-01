import json
import Crypto
from Crypto import Random
from Crypto.Cipher import AES
import os
from core.conditions.conditionChecker import ConditionChecker
from core.filewatcher import FileWatcher
from core.parsers.jsonparser import JsonParser
from core.parsers.xmlparser import XmlParser
from core.utils import logger, AESCipher
from core.profiler import profiler
from time import sleep
import core.api.api as api
import sys

class Watcher(FileWatcher):
    seenfiles = []
    def __init__(self, incommingFiles):
        self.DetectedFiles = incommingFiles
        FileWatcher.__init__(self)

    def callback(self, file):
        if file not in self.seenfiles:
            self.DetectedFiles.append(file)
            self.seenfiles.append(file)


profileId = 9  # TODO: Read from config
conditionId = 1  # TODO: Read from config
incomingFiles = list()
fileWatcher = Watcher(incomingFiles)
stopping = False
logger = logger()
p = profiler(logger)
seen_data = []
a = api.API(logger=logger)
config = json.load(open('config/default.config.json'))

if 'internal' in config['encryptionKeys']:
    internalAESEncrypter = AESCipher(config['encryptionKeys']['internal']['key'], config['encryptionKeys']['internal']['IV'])
else:
    internalAESEncrypter = None

if 'input' in config['encryptionKeys']:
    inputAESEncrypter = None #AESCipher(config['encryptionKeys']['input']['key'], config['encryptionKeys']['input']['IV'])
else:
    inputAESEncrypter = None

#if __debug__:
#    with open('config/condition.config.json', 'r') as file:
#        conditionOutput = internalAESEncrypter.encrypt(file.read())
#    with open('config/condition.config.json', 'w') as outputFile:
#        outputFile.write(conditionOutput.decode("utf-8"))
#
#    with open('config/parser.config.json', 'r') as file:
#        parserOutput = internalAESEncrypter.encrypt(file.read())
#    with open('config/parser.config.json', 'w') as outputFile:
#        outputFile.write(parserOutput.decode("utf-8"))
#
#    with open('input/test.json', 'r') as file:
#        inputOutput = internalAESEncrypter.encrypt(file.read())
#    with open('input/test.json', 'w') as outputFile:
#        outputFile.write(inputOutput.decode("utf-8"))

while not stopping:
    fileName = None
    newresults = a.jobs('input')
    for result in newresults:
        incomingFiles.append("%s,%s,%s" % (result['file'], result['datasource'], result['condition_list']))
    # Check if a new file is added and get it from the list
    try:
        if len(incomingFiles) > 0:
            file = incomingFiles.pop(0)
            if file in seen_data: continue
            seen_data.append(file)
            parser = object
            if ',' in file:
                fileName = file
                file, profileId, conditionId = file.split(',')

            logger.log("[Main] Start processing file: {}".format(file))

            # Update profile config from API
            with open('config/parser.config.json', 'w') as output:
                data = json.loads(api.API().getDatasources(profileId))
                if internalAESEncrypter is not None:
                    output.write(internalAESEncrypter.encrypt(json.dumps({"type": data["type"], "fields": data["data"]})).decode("utf-8"))
                else:
                    output.write(json.dumps({"type": data["type"], "fields": data["data"]}))

            # Update conditions config from API
            with open('config/condition.config.json', 'w') as output:
                if internalAESEncrypter is not None:
                    output.write(internalAESEncrypter.encrypt(json.dumps({"conditions": json.loads(api.API().getConditions(conditionId))["data"]})).decode("utf-8"))
                else:
                    output.write(json.dumps({"conditions": json.loads(api.API().getConditions(conditionId))["data"]}))

            # Determining file extension and assigning appropriate parser
            fileExtension = file.split('.')[-1:][0]

            if fileExtension == 'json':
                parser = JsonParser('config/parser.config.json', 'output/', logger, inputAESEncrypter, internalAESEncrypter)
            elif fileExtension == 'xml':
                parser = XmlParser('config/parser.config.json', 'output/', logger, inputAESEncrypter, internalAESEncrypter)
            else:
                logger.log("[Main] Input file extension is not recognized. Following extension is found: {}".format(
                    fileExtension))
                continue

            filePath = parser.parse(file)

            conditionChecker = ConditionChecker('config/condition.config.json', logger, internalAESEncrypter)
            warnings, score = conditionChecker.checkConditions(filePath)

            # create HTML profile and post to remote server (encrypted)
            p.report_profile(file, int(score), warnings)
            os.remove(file)
    except Exception as ex:
        msg = "[Main] Error, message: {}".format(ex)
        logger.log(msg)
    sleep(5)

logger.log("Stopped!")
