import json
import os.path

from core.parsers.parser import Parser


# For parsing JSON files into configurable JSON format
class JsonParser(Parser):

    # params: [configPath]: full file path JSON configuration file
    #         [outputPath]: full file path for the desired location of the output JSON
    def __init__(self, configPath, outputPath, logger=None, inputEncrypter=None, internalEncrypter=None):
        self.init(configPath, outputPath, logger, inputEncrypter, internalEncrypter)

    # parse function for parsing the incoming JSON file into a new JSON file
    # params: [dataFilePath]: full file path for the incoming JSON data
    def parse(self, dataFilePath):
        try:
            data = json.loads(self.readInput(dataFilePath))
        except json.decoder.JSONDecodeError as ex:
            msg = "[JSONParser] Incorrect json file, message: {}".format(ex)
            self.logger.log(msg, 1)
            raise Exception(msg)

        outputData = {"profile": {}}

        for configField in self.getInputFields():
            try:
                levels = configField["position"].split(".")
                value = data

                for level in levels:
                    value = value[level]

                outputField = {}
                outputField["value"] = value
                outputField["type"] = configField["type"]
                outputData["profile"][configField["name"]] = outputField
            except KeyError as ex:
                msg = "[JSONParser] A key is not found in given data, message: {}".format(ex)
                self.logger.log(msg, 2)

        outputFilePath = "{}/{}.json".format(self.getOutputPath(), os.path.splitext(os.path.basename(dataFilePath))[0])

        if not os.path.exists(os.path.dirname(outputFilePath)):
            os.makedirs(os.path.dirname(outputFilePath))

        self.writeToProfile(outputData, outputFilePath)
        return outputFilePath
