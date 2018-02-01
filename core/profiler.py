import core.utils
from core.api.api import API
import datetime
import json


class profiler:
    #  construct the HTML engine
    htmlEngine = core.utils.HTMLEngine()

    #  API, uses existing if available
    api = None

    #  the logger class
    logger = None

    #  used for risk calculation (can be tweaked)
    risk_values = {10, 25, 50, 100}  # < 10 no risk | < 25 low risk | < 50 med risk | else high risk

    def __init__(self, logger=None, existingApi=None):
        if not existingApi:
            self.api = API()
            self.api.logger = logger
        self.logger = logger

    def calculateRiskLevel(self, number):
        currentRiskValue = 4
        for value in self.risk_values:
            #  get the highest risk value possible
            if number >= value:
                currentRiskValue -= 1
        return currentRiskValue if currentRiskValue > 0 else 1

    # warning = [Field name, Condition Type, Validated Against, Current Risk Factor]
    def report_profile(self, profileName, riskNumber, detectedWarnings=[], addText=""):
        riskValue = self.calculateRiskLevel(riskNumber)

        # insert the header row in the warning list
        detectedWarnings.insert(0, ['Field name', 'Condition Type', 'Validated Against', 'Current Risk Factor'])
        profileData = self.htmlEngine.generateProfile('The system analysed the profile of: '
                                                      '<strong>%s</strong> on %s and found <strong>%d</strong> potential risk factor(s), '
                                                      'the overal risk score calculated is %d<br/>%s<hr>' % (
                                                      profileName, datetime.datetime.now(), len(detectedWarnings) - 1,
                                                      riskNumber, addText), detectedWarnings, riskValue)

        if self.logger:
            self.logger.log("[Profiler] Generated %d of bytes for profile, starting secure upload" % len(profileData),
                            3)

        # encryption

        preparedData = self.api.prePost('Profile of %s' % profileName)
        # just crash if keys are not found, saves a lot of time adding checks
        try:
            resultData = json.loads(preparedData)
            data = resultData['data']
            id = data['id']
            password = data['key']
            b64iv = data['IV']
            validationKey = data['keyVerificationToken']

            if self.logger:
                self.logger.log("[Profiler] Remote system assigned %d as the profile number" % id, 3)

            # encrypt the result
            encodedResult = core.utils.AESCipher(password, b64iv).encrypt(profileData)

            if self.logger:
                self.logger.log("[Profiler] Encryption using AES-265-CFB8 resulted in %d bytes of encrypted data" % len(
                    encodedResult), 3)

            result = self.api.pushResultData(id, validationKey, encodedResult)

            if result:
                if self.logger:
                    self.logger.log("[Profiler] Profile data successfully posted to profile number: %d" % id, 3)

        except:
            if self.logger:
                self.logger.log("[Profiler] Error encoding profile data, cannot store remotely :(", 2)
            pass
