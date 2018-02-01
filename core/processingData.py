import re
import datetime
import core.conditions.general as general
from api.api import postalCodeAPI as postalCodeApi


# Function to check if there are numbers
def hasNumbers(inputString):
    return bool(re.search(r'\d', inputString))


# Function to add a 0 before if needed
def addZero(stringlen, stringToChange):
    return '0'*(stringlen - len(stringToChange)) + stringToChange


# Function to upper names except some middle names
def titleExcept(name, exceptions):
    wordlist = re.split(' ', name)
    final = [wordlist[0].capitalize()]
    for word in wordlist[1:]:
        final.append(word if word in exceptions else word.capitalize())
    return " ".join(final)


# Function to normalize postal code and check the postal code with the given street
def checkPostalCode(postalCode, streetName, streetNumber):
    pattern = re.compile("^(\d{4})[ -]?([a-zA-Z]{2})$")
    patternData = pattern.match(postalCode)

    if patternData:
        postalCode = patternData.group(1) + patternData.group(2).upper()
        postalCodeResult = postalCodeApi().getAddress(postalCode, streetNumber)

        if postalCodeResult is None:
            raise Exception('Invalid postal code')
        if streetName.lower() != str([postalCodeResult[0]]).strip("[]'").lower():
            raise Exception('Invalid street in given postal code')
        else:
            return postalCode
    else:
        raise Exception('Invalid postal code format')


# Function to check input and set the first char to upper
def setStringToTitle(name):
    if general.stringTryParse(name):
        if not hasNumbers(name):
            articles = ['de', 'van', 'der', 'den', 'la', 'des']
            return titleExcept(name, articles)
        else:
            raise Exception("Invalid character(s) found")
    else:
        raise Exception("Invalid input type, only String is allowed")


# Function to check the email address
def checkEmail(email):
    if not general.stringTryParse(email):
        raise Exception("Invalid input type, only String is allowed")

    pattern = re.compile("^([\w\.-]+\@[\w\.-]+\.[\w\.-]+)$")
    patternData = pattern.match(email.lower())

    if patternData:
        return patternData.group(1)
    else:
        raise Exception("Invalid email address")


# Function to normalize the date of birth and return the date of birth as string
def normalizeDateOfBirth(date):
    r = detectDateFormat(date)
    if not r:
        return False

    day, month, year = r.split("-")

    if day <= 0 or day > 31:
        raise Exception("Invalid day")

    if month <= 0 or month > 12:
        raise Exception("Invalid month")

    if year <= 1900 or year > datetime.datetime.now().year:
        raise Exception("Invalid year")

    dayStr = str(day)
    monthStr = str(month)

    return addZero(2, dayStr) + "-" + addZero(2, monthStr) + "-" + str(year)


def detectDateFormat(date):
    date_patterns = ["%d-%m-%Y", "%Y-%m-%d", "%d-%m-%Y", "%d/%m/%Y", "%Y/%m/%d", "%d %b %Y", "%d %B %Y", "%d %B, %Y", "%d %b, %Y"]
    for pattern in date_patterns:
        try:
            return datetime.datetime.strptime(date, pattern).date().strftime('%d-%m-%Y')
        except:
            pass
    return None
