var app = angular.module('app', [])

	.filter("join", [function() {
		return function(array, glue){
			return array.join(glue);
		};
	}])

	.filter("trimComment", function(){
		return function(comment){
			return comment.replace(/^\s*/gm, '');
		};
	})

	.controller('ctrl', function($scope) {
		chrome.devtools.network.onRequestFinished.addListener(function(request) {
			request.response.headers.forEach(function(header){
				// this may not be the header we are looking for
				if(header.name !== 'X-INHERITANCE-CHAIN') return;

				$scope.url = request.request.url;
				$scope.dumps = JSON.parse(header.value);
				console.log($scope.dumps);
			});
			$scope.$apply();
		});

		$scope.showDocBlock = function($method){
			console.log($method);
			if(!$method.comment){
				$scope.doc_block = '';
				return;
			}
			$scope.doc_block = $method.comment;
		};
	})

	.controller('dump', function($scope) {
		$scope.showDocBlock = function($method){
			if(!$method.comment){
				$scope.doc_block = '';
				return;
			}
			$scope.doc_block = $method.comment;
		};
	});