import json


# Abstract class for parsing files into configurable JSON format
class Parser(object):
    def init(self, configPath, outputPath, logger, inputEncrypter=None, internalEncrypter=None):
        self.__outputPath = outputPath
        self.__inputFields = list()
        self.logger = logger
        self.inputAESEncrypter = inputEncrypter
        self.internalAESEncrypter = internalEncrypter
        self.loadConfig(configPath)

    def getOutputPath(self):
        return self.__outputPath

    # params: [outputPath]: full path for the output JSON file
    def setOutputPath(self, outputPath):
        self.__outputPath = outputPath

    def getInputFields(self):
        return self.__inputFields

    # params: [configPath]: full path for the JSON configuration file
    def loadConfig(self, configPath):
        if self.internalAESEncrypter is not None:
            with open(configPath, 'r') as file:
                data = json.loads(self.internalAESEncrypter.decrypt(file.read()))
        else:
            data = json.load(open(configPath))

        for field in data["fields"]:
            self.__inputFields.append(field)

    def parse(self, dataFilePath):
        pass

    def readInput(self, filePath):
        if self.inputAESEncrypter is not None:
            with open(filePath, 'r') as file:
                data = self.inputAESEncrypter.decrypt(file.read())
        else:
            data = open(filePath).read()

        return data

    def writeToProfile(self, data, filePath):
        with open(filePath, 'w') as outputFile:
            if self.internalAESEncrypter is not None:

                file = open(filePath, 'w')

                file.write(self.internalAESEncrypter.encrypt(json.dumps(data)).decode("utf-8"))

                file.close()

            else:
                json.dump(data, outputFile)
