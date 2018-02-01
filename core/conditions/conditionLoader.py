import json
from core.conditions.condition import Condition, ConditionType, OperationType, MultiplierType
from core.utils import logger

class ConditionLoader:
    """
    Loads and validates a conditions file

    Args:
        configPath          File location of config

    Attributes:
        conditionConfig     JSON representation of config file
        configPath          File location of config
        conditions          Array of Condition containing business rules
    """

    def __init__(self, configPath, logger=None, internalAESEncrypter=None):
        self.logger = logger
        self.conditionConfig = None
        self.configPath = configPath
        self.conditions = []
        self.internalAESEncrypter = internalAESEncrypter

        self.loadConditions()

    def loadConditions(self):
        with open(self.configPath, 'r') as config:
            try:
                if self.internalAESEncrypter is not None:
                    self.conditionConfig = json.loads(self.internalAESEncrypter.decrypt(config.read()))
                else:
                    self.conditionConfig = json.loads(config.read())
            except json.decoder.JSONDecodeError as ex:
                self.logger.log("Invalid JSON detected whe loading {}, error: {}".format(self.configPath, ex.msg), 1)
                raise  # Rethrow after logging exception

            conditions = self.conditionConfig["conditions"]
            for condition in conditions:
                try:
                    fieldName = condition["fieldName"]
                    operation = OperationType(condition["operation"])
                    conditionType = ConditionType(condition["conditionType"])
                    val = condition["condition"]
                    multiplierType = MultiplierType(condition["multiplier_type"])
                    multiplier = condition["multiplier_value"]

                    if not (len(fieldName) and operation and conditionType and val and multiplierType):
                        msg = "One or more fields is empty in: {}".format(condition)
                        self.logger.log(msg, 1)
                        raise Exception(msg)

                    self.conditions.append(Condition(fieldName, operation, conditionType, val, multiplierType, float(multiplier)))
                except KeyError as ex:  # thrown when OperationType or ConditionType are not valid
                    msg = "Invalid configuration detected when loading {}, field: {}".format(self.configPath, ex)
                    self.logger.log(msg, 1)
                    raise Exception(msg)

                pass
        pass


"""
logr = logger()
logr.log("Log entry", 1)

x = ConditionLoader("C:\\Users\\wesse\\PycharmProjects\\ics-fds\\conditions.json", logr)

for c in x.conditions:
    print(c.fieldName)
    print(c.operation)
    print(c.conditionType)
    print(c.condition)
    print()
"""
