import core.conditions.general as general


# Check conditions for int and double values

# Function to check if the valueToCheck is in range between 2 values,
# first entry in val is the lowest value, the other value is the highest value
def checkRange(val, valueToCheck):
    if type(val) is not list:
        raise Exception('Invalid range type')

    lowestValue = val[0]
    highestValue = val[1]

    if general.intTryParse(lowestValue) & general.intTryParse(highestValue) & general.intTryParse(valueToCheck):
        return valueToCheck >= lowestValue and valueToCheck <= highestValue
    else:
        raise Exception('Invalid character(s) or type found')


# Function to check if the valueToCheck is lower than the givenValue
def lessThan(valueToCheck, givenValue):
    if general.intTryParse(givenValue) & general.intTryParse(valueToCheck):
        return valueToCheck < givenValue
    else:
        raise Exception('Invalid character(s) or type found')


# Function to check if the valueToCheck is equal or lower than the givenValue
def lessOrEqualsThan(valueToCheck, givenValue):
    if general.intTryParse(givenValue) & general.intTryParse(valueToCheck):
        return valueToCheck <= givenValue
    else:
        raise Exception('Invalid character(s) or type found')


# Function to check if the valueToCheck is greater than the givenValue
def greaterThan(valueToCheck, givenValue):
    if general.intTryParse(givenValue) & general.intTryParse(valueToCheck):
        return valueToCheck > givenValue
    else:
        raise Exception('Invalid character(s) or type found')


# Function to check if the valueToCheck is equal or greater than the givenValue
def greaterOrEqualsThan(valueToCheck, givenValue):
    if general.intTryParse(givenValue) & general.intTryParse(valueToCheck):
        return valueToCheck >= givenValue
    else:
        raise Exception('Invalid character(s) or type found')
