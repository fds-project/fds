import requests
import random
import json
import os
import time
class logger_local:
    def log(self, text, sev=1):
        print("%d> %s" % (sev, text))

#  small API call to postcodeapi.nu
class postalCodeAPI:
    r = requests.session()
    #  https://www.postcodeapi.nu/registreren/free/
    keys = ['LS4rjfQgkl7qww8AmjKD44hOf3N4PzstOyJNjKB9', 'IRhA3zWg9D6dNGIA4Wk5H6XPcD17ucuB1IxeOruk', 'Yw8oJxMh9c2JOk4rpE3nBanygl0DpzNW8MRJQSv8', 'lN0nfwlaMoSzhxArY4or4C8e3PtfUEe3bZ3uBvDh']
    def getAddress(self, postalCode, number):
        try:
            #  set headers with random API key
            headers = {
                "accept": "application/hal+json",
                "x-api-key": random.choice(self.keys)
            }
            url = "https://api.postcodeapi.nu/v2/addresses/?postcode=%s&number=%s" % (postalCode, number)

            r = requests.get(url, headers=headers)
            #  if data is None / error return None
            if not r:
                return
            jsondata = json.loads(r.text)
            information = jsondata['_embedded']['addresses'][0]
            return [information['street'], information['city']['label']]
        except:
            #  if data is None / error return None
            return


#  API wrapper class
class API:
    #  version sent with each request (for debugging)
    api_version = "FDS v1.0"

    #  global API token
    token = "8308651804facb7b9af8ffc53a33a22d6a1c8ac2"

    #  remote server
    remote_url = "http://localhost/fds/parser.php"

    #  remote server extended api (encryption support)
    remote_url_ext = "http://localhost/fds/parser_extended.php"

    #  static session holder
    weblib = None

    #  logger class
    logger = None

    #  use 1 session to improve performance
    use_session = True

    #  if no logger is present local backup logger will be used
    def __init__(self, logger=logger_local(), token=None):
        self.logger = logger
        if token:
            self.token = token
        if self.use_session:
            self.logger.log("[API] Will use static session for calls (faster)", 3)
            self.weblib = requests.session()

    #  send GET request
    def get(self, action, data={}, extended_api=False):
        #  get global request data
        data['action'] = action
        data['authKey'] = self.token
        data['client-version'] = self.api_version

        #  always close connections (for faster database transactions)
        headers = {'Connection': 'Close'}

        if self.weblib:
            r = self.weblib.get(self.remote_url if not extended_api else self.remote_url_ext, params=data, headers=headers)
            if r:
                self.logger.log("[API GET] %s" % r.url, 3)
            else:
                self.logger.log("[API GET] Error getting %s" % r.url, 1)
            return r
        r = requests.get(self.remote_url if not extended_api else self.remote_url_ext, params=data, headers=headers)
        if r:
            self.logger.log("[API GET] %s" % r.url, 3)
        else:
            self.logger.log("[API GET] Error getting %s" % r.url, 1)
        return r

    #  send POST request
    def post(self, action, data={}, postdata={}, extended_api=False):
        #  get global request data
        data['action'] = action
        data['authKey'] = self.token
        data['client-version'] = self.api_version

        #  always close connections (for faster database transactions)
        headers = {'Connection': 'Close'}

        if self.weblib:
            r = self.weblib.post(self.remote_url if not extended_api else self.remote_url_ext, params=data, data=postdata, headers=headers)
            if r:
                self.logger.log("[API GET] %s" % r.url, 3)
            else:
                self.logger.log("[API GET] Error getting %s" % r.url, 1)
            return r
        r = requests.post(self.remote_url if not extended_api else self.remote_url_ext, params=data, data=postdata, headers=headers)
        if r:
            self.logger.log("[API POST] %s" % r.url, 3)
        else:
            self.logger.log("[API POST] Error getting %s" % r.url, 1)
        return r

    #  prepare the remote server (generate AES keys / IV blocks)
    def prePost(self, name="untitled"):
        result = self.get('prepost', data={'name': name}, extended_api=True)
        if result:
            return result.text
        return []

    #  send the actual data, encode with the key / IV received from the prePost function
    def pushResultData(self, id, authkey, encodedPostdata, encoding='base64'):
        result = self.post('updateprofile', data={'id': id, 'keyVerificationToken': authkey, 'encoding': encoding}, postdata={'data': encodedPostdata}, extended_api=True)
        if result:
            return result.text
        return []

    #  get dem datasources
    def getDatasources(self, id=1):
        result = self.get('getDatasources', data={'id': id})
        if result:
            return result.text
        return []

    #  get conditions JSON from remote source
    def getConditions(self, forGroup=1):
        result = self.get('getConditions', data={'group_id': forGroup})
        if result:
            return result.text
        return []
    # get remote jobs
    def getJobs(self):
        result = self.get('getJobs')
        if result:
            return json.loads(result.text)
        return None
    # set the remote status of a job
    def setJob(self, job_id, new_status):
        self.get('jobSetStatus', data={'id': job_id, 'status': new_status})

    def jobs(self, output_directory):
        result = self.getJobs()
        whitelist = ['json', 'xml', 'csv', 'txt']
        results = []
        if not os.path.exists(output_directory):
            # not in project root, assuming 1 directory up
            output_directory = "..//%s" % output_directory
        if result['result']:
            for entry in result['data']:
                jobid = entry['id']
                datatype = entry['type']
                if datatype not in whitelist:
                    self.logger.log('[JOB] invalid datatype: %s, the entry is not in the white-list: %s' % (datatype, ','.join(whitelist)), 2)
                datasource = entry['datasourceid']
                conditionlist = entry['conditiongroupid']
                data = entry['text']
                # iv = config['encryption']['input']['iv']
                # key = config['encryption']['input']['key']
                # data = AES.encrypt(data, iv, key)
                file = "%s//remote_%s.%s" % (output_directory, jobid, datatype)
                with open(file, 'w') as f:
                    f.write(data)
                self.logger.log('[JOB] job loader write %d bytes to input file: %s' % (len(data), file), 3)
                results.append({'id': jobid, 'datatype': datatype, 'datasource': datasource, 'condition_list': conditionlist, 'file': file})
                self.setJob(jobid, 1)
                time.sleep(2)
            return results
        else:
            self.logger.log('[JOB] received invalid response getting jobs', 2)
            return []
        # if magic happens
        return []
