import json

from core.conditions.condition import Condition, ConditionType, MultiplierType
from core.conditions.conditionLoader import ConditionLoader


class ConditionChecker(object):
    """
        Represents a condition checker used to check on conditions in a profile

        Attributes:
            conditions      All conditions

        Args:
            conditionConfig relative path to the condition config
    """

    def __init__(self, conditionConfig, logger=None, internalEncrypter=None):
        self.internalAESEncrypter = internalEncrypter
        self.conditions = ConditionLoader(conditionConfig, logger, internalEncrypter).conditions
        self.logger = logger

    # Check given profile on the conditions [params]: dataFilePath: relative path to the profile json
    def checkConditions(self, dataFilePath):
        if self.internalAESEncrypter is None:
            data = json.load(open(dataFilePath))
        else:
            with open(dataFilePath, 'r') as file:
                data = json.loads(self.internalAESEncrypter.decrypt(str(file.read())))

        profile = data["profile"]
        score = 1
        warnings = []
        conditions = []

        for condition in self.conditions:
            try:
                checkingValue = ""

                if condition.conditionType == ConditionType.value:
                    checkingValue = condition.condition
                else:
                    checkingValue = profile[condition.condition]["value"]

                result = Condition.functions[condition.operation](checkingValue, profile[condition.fieldName]["value"])
                if result:
                    previousScore = score

                    if condition.multiplierType is MultiplierType.none:
                        warnings.append([condition.fieldName, str(condition.operation), checkingValue, 'Warning only'])
                    elif condition.multiplierType is MultiplierType.divide:
                        score /= condition.multiplier
                    elif condition.multiplierType is MultiplierType.multiply:
                        score *= condition.multiplier
                    elif condition.multiplierType is MultiplierType.add:
                        score += condition.multiplier
                    elif condition.multiplierType is MultiplierType.subtract:
                        score -= condition.multiplier

                    score = int(score)

                    if score > 100:
                        score = 100

                    warnings.append([condition.fieldName, str(condition.operation), checkingValue, 'from {} to {} (func:{} val:{}'.format(previousScore, score, condition.multiplierType, condition.multiplier)])
                    self.logger.log("[ConditionChecker] Score for {} compared with {} value {} changed from {} to {}" \
                                    .format(condition.fieldName, condition.operation, checkingValue, previousScore, score), 3)

                # Added condition results to end data in profile
                conditionResult = {}
                conditionResult["fieldName"] = condition.fieldName
                conditionResult["operation"] = condition.operation.value
                conditionResult["checkingValue"] = checkingValue
                conditionResult["originalValue"] = profile[condition.fieldName]["value"]
                conditionResult["result"] = result
                conditions.append(conditionResult)

            except KeyError as ex:
                msg = "[ConditionChecker] Condition field is not found in the profile, message: {}".format(ex)
                self.logger.log(msg, 2)

        data["profile"]["score"] = score
        data["conditions"] = conditions
        data["warnings"] = warnings

        with open(dataFilePath, 'w') as outputFile:
            if self.internalAESEncrypter is None:
                json.dump(data, outputFile)
            else:
                outputFile.write(self.internalAESEncrypter.encrypt(json.dumps(data)).decode("utf-8"))

        return warnings, score

