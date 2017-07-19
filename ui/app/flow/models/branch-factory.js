angular.module('continuousPipeRiver').factory("Branch", function($firebaseUtils, latestTideInBranchFilter, TideSummaryRepository) {
    function Branch(snapshot) {
        // store the record id so AngularFire can identify it
        this.$id = snapshot.key;

        // apply the data
        this.update(snapshot);

        var latestTide = latestTideInBranchFilter(this);
        var self = this;
        if (latestTide) {
            TideSummaryRepository.findByTide(latestTide).then(function(summary) {
                self.environment = summary.environment;
            });
        }
    }

    Branch.prototype = {
        update: function(snapshot) {
            var oldData = angular.extend({}, this.data);

            this.data = snapshot.val();

            return !angular.equals(this.data, oldData);
        },

        toJSON: function() {
            return $firebaseUtils.toJSON(this.data);
        }
    };

    return Branch;
});

// now let's create a synchronized array factory that uses our Widget
angular.module('continuousPipeRiver').factory("BranchFactory", function($firebaseArray, Branch) {
    return $firebaseArray.$extend({
        // change the added behavior to return Widget objects
        $$added: function(snap) {
            // instead of creating the default POJO (plain old JavaScript object)
            // we will return an instance of the Widget class each time a child_added
            // event is received from the server
            return new Branch(snap);
        },

        // override the update behavior to call Widget.update()
        $$updated: function(snap) {
            // we need to return true/false here or $watch listeners will not get triggered
            // luckily, our Widget.prototype.update() method already returns a boolean if
            // anything has changed
            return this.$getRecord(snap.key()).update(snap);
        }
    });
});