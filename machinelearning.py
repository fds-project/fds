import core.api.api as api
import sys, os
import json
import numpy as np
#  check for direct call
if "machinelearning" not in sys.argv[0]:
    print("Warning, due to the heavy load time caused by the Tensorflow binary this module can only run directly!")
    sys.exit(0)

#  import sklearn stuff
from sklearn.model_selection import train_test_split
from sklearn.tree import DecisionTreeClassifier
from sklearn import metrics
from sklearn.externals import joblib

modelPath =  'fds_model.pkl'

#  what to do..
allowedactions = ['train', 'test', 'run']
if len(sys.argv) > 1:
    action = sys.argv[1].replace('--', '')
    if action.strip() not in allowedactions:
        print("Invalid action %s, allowed are: %s" % (action, ','.join(allowedactions)))
    if action is 'test':
        if not os.path.exists(modelPath):
            print("No trained model found, exiting")
            sys.exit(0)
        model = joblib.load(modelPath)
        if not model:
            print("Error loading trained model!!")
            sys.exit(0)

        data = sys.argv[2].strip()
        subset = []
        for character in range(0, len(data)):
            #  intcast everything because data should be in binary format
            subset.append(int(data[character]))

        result = model.predict(subset)
        #  what to do with result?
        print(result)
        sys.exit(0)

#  training set id
data_id = 1

a = api.API()
#  get training data using the API
getTrainingData = a.get('getTrainingData', {'group_id': data_id})
data = json.loads(getTrainingData.text)
#  some checks :)
if data['result'] is not True:
    print("Invalid call, stopping")
    sys.exit(0)

tdata = data['data']
if len(tdata) is 0:
    print("Invalid dataset (len 0), stopping")
    sys.exit(0)

#  start building X,y set
trainingDataX = []
trainingDataY = []
subsetlength = None
for row in tdata:
    subset = []
    result = row['binaryresultstring']
    #  use the first row length as default length
    if subsetlength is None: subsetlength = len(result)
    for character in range(0, len(result)):
        #  intcast everything because data should be in binary format
        subset.append(int(result[character]))
    #  skip entries with invalid length
    if len(subset) is not subsetlength:
        print("Length not in correct format, skipping %s" % result)
        continue
    trainingDataX.append(subset)
    trainingDataY.append(int(row['ylabel']))

#  build the testing-training set
X_train, X_test, y_train, y_test = train_test_split(trainingDataX, trainingDataY, test_size=0.33, random_state=42)

#  create numpy array (float casting)
X_train = np.array(X_train)
y_train = np.array(y_train)

#  build the model with depth of 3 (best fit for training data)
#  min samples leaf: 5 to create training set best fit for entropy criterion
#  entropy criterion instead of gini because is has better cross-validation because we have no N/A labels
tree = DecisionTreeClassifier(criterion='entropy', max_depth=3, min_samples_leaf=5)
clf = tree.fit(X_train, y_train)

#  copied from stackoverflow to print model accuracy
def measure_performance(X, y, clf, show_accuracy=True, show_classification_report=True, show_confusion_matrix=True):
    y_pred = clf.predict(X)
    if show_accuracy:
        print("Accuracy:{0:.3f}".format(metrics.accuracy_score(y, y_pred)), "\n")

    if show_classification_report:
        print("Classification report")
        print(metrics.classification_report(y, y_pred), "\n")

    if show_confusion_matrix:
        print("Confusion matrix")
        print(metrics.confusion_matrix(y, y_pred), "\n")


measure_performance(X_train, y_train, clf, show_classification_report=True, show_confusion_matrix=True)
#  dump the trained model to file
joblib.dump(clf, modelPath)