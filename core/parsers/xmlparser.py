import xml.etree.ElementTree as ET
import os.path

from core.parsers.parser import Parser


# For parsing XML files into configurable JSON format
class XmlParser(Parser):

    # params: [configPath]: full file path JSON configuration file
    #         [outputPath]: full file path for the desired location of the output JSON
    def __init__(self, configPath, outputPath, logger=None, inputEncrypter=None, internalEncrypter=None):
        self.init(configPath, outputPath, logger, inputEncrypter, internalEncrypter)

    # parse function for parsing the incoming XML file into a new JSON file
    # params: [dataFilePath]: full file path for the incoming XML data
    def parse(self, dataFilePath):
        outputData = {"profile": {}}

        try:
            data = ET.parse(self.readInput(dataFilePath)).getroot()
        except ET.ParseError as ex:
            msg = "[XMLParser] Error while parsing xml file, message: {}".format(ex)
            self.logger.log(msg, 1)
            raise Exception(msg)

        for configField in self.getInputFields():
            try:
                levels = configField["position"].split(".")
                value = data

                for level in levels:
                    value = value.find(level)

                outputField = {}
                outputField["value"] = value.text
                outputField["type"] = configField["type"]
                outputData["profile"][configField["name"]] = outputField
            except KeyError as ex:
                msg = "[XMLParser] A key is not found in given data, message: {}".format(ex)
                self.logger.log(msg, 2)

        outputFilePath = "{}/{}.json".format(self.getOutputPath(), os.path.splitext(os.path.basename(dataFilePath))[0])

        if not os.path.exists(os.path.dirname(outputFilePath)):
            os.makedirs(os.path.dirname(outputFilePath))

        self.writeToProfile(outputData, outputFilePath)
        return outputFilePath
