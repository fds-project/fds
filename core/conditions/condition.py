from enum import Enum
from core.conditions import number, string
from core.conditions import general


class OperationType(Enum):
    # global operation types
    equals = "equals"
    notEquals = "notEquals"
    contains = "contains"

    # integer operation types
    checkRange = "checkRange"
    lessThan = "lessThan"
    lessThanOrEquals = "lessThanOrEquals"
    greaterThan = "greaterThan"
    greaterThanOrEquals = "greaterThanOrEquals"

    # string operation types
    regex = "regex"
    empty = "empty"
    notEmpty = "notEmpty"
    length = "length"
    minLength = "minLength"
    maxLength = "maxLength"

    # boolean operation types
    isTrue = "isTrue"
    isFalse = "isFalse"


class MultiplierType(Enum):
    add = "add"
    subtract = "sub"
    divide = "div"
    multiply = "mul"
    none = "none"


class Condition(object):
    """
    Represents a condition used to define a business rule

    Attributes:
        fieldName       Name of the field to retrieve the value from
        operationType   Determines what kind of operation is used for logic.
        conditionType   Determines whether the value of condition represents another field or a value
        condition       Value to compare for

        functions       [STATIC] represents all the different operations linked to the methods
    """

    functions = {
        OperationType.equals: general.equals,
        OperationType.notEquals: general.notEquals,
        OperationType.contains: general.contains,
        OperationType.isTrue: general.isTrue,
        OperationType.isFalse: general.isFalse,
        OperationType.checkRange: number.checkRange,
        OperationType.lessThan: number.lessThan,
        OperationType.lessThanOrEquals: number.lessOrEqualsThan,
        OperationType.greaterThan: number.greaterThan,
        OperationType.greaterThanOrEquals: number.greaterOrEqualsThan,
        OperationType.regex: string.regex,
        OperationType.empty: string.empty,
        OperationType.notEmpty: string.notEmpty,
        OperationType.length: string.length,
        OperationType.minLength: string.minLength,
        OperationType.maxLength: string.maxLength
    }

    def __init__(self, fieldName, operation, conditionType, condition, multiplierType, multiplier):
        self.fieldName = fieldName
        self.operation = operation
        self.conditionType = conditionType
        self.condition = condition
        self.multiplierType = multiplierType
        self.multiplier = multiplier


class ConditionType(Enum):
    value = "value"
    field = "field"

