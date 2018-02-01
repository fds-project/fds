# Check conditions for general conditions

# Try parsing to int
def intTryParse(value):
    try:
        int(value)
        return True
    except ValueError:
        return False


# Try parsing to String
def stringTryParse(value):
    if type(value) is str:
        return True
    else:
        return False


# Function to check if the data equals the given data
def equals(dataToCheck, givenData):
    if intTryParse(dataToCheck) or stringTryParse(dataToCheck):
        if intTryParse(dataToCheck) or stringTryParse(dataToCheck):
            return dataToCheck == givenData
        else:
            raise Exception("Invalid character(s) or type found")
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check if the data doesn't equals the given data
def notEquals(dataToCheck, givenData):
    if intTryParse(dataToCheck) or stringTryParse(dataToCheck):
        if intTryParse(givenData) or stringTryParse(dataToCheck):
            return dataToCheck != givenData
        else:
            raise Exception("Invalid character(s) or type found")
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check if the data contains something of the given data
def contains(dataToCheck, givenData):
    if intTryParse(dataToCheck) or stringTryParse(dataToCheck):
        if intTryParse(givenData) or stringTryParse(dataToCheck):
            return dataToCheck in givenData
        else:
            raise Exception("Invalid character(s) or type found")
    else:
        raise Exception("Invalid character(s) or type found")


# Function to check if the condition is true
def isTrue(dataToCheck):
    return dataToCheck.IsTrue


# Function to check if the condition is false
def isFalse(dataToCheck):
    return dataToCheck.IsFalse
