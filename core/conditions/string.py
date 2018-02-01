import re
import core.conditions.general as general


# Function to check a string with a given regex
def regex(regex, stringToCheck):
    try:
        re.compile(regex)
    except re.error:
        raise Exception(re.error)
    pattern = re.compile(regex)
    if general.stringTryParse(stringToCheck):
        return pattern.match(stringToCheck)
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check if a string is empty
def empty(stringToCheck):
    if general.stringTryParse(stringToCheck):
        return not stringToCheck or len(stringToCheck) is 0
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check if a string not empty
def notEmpty(stringToCheck):
    if general.stringTryParse(stringToCheck):
        return stringToCheck and len(stringToCheck) > 0
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check the length from a given string
def length(stringToCheck):
    if general.stringTryParse(stringToCheck):
        return len(stringToCheck)
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check if the length from a string is greater than the given length
def minLength(givenLength, stringToCheck):
    if general.intTryParse(givenLength) & general.stringTryParse(stringToCheck):
        return givenLength > len(stringToCheck)
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check if the length from a string is smaller than the given length
def maxLength(givenLength, stringToCheck):
    if general.intTryParse(givenLength) & general.stringTryParse(stringToCheck):
        return givenLength < len(stringToCheck)
    else:
        raise Exception("Invalid character(s) or type found")
