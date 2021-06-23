sonar:
	docker run -ti -v $(shell pwd):/usr/src/ pagarme/sonar-scanner -Dsonar.branch.name=${BRANCH}
.PHONY: sonar

sonar-check-quality-gate:
	docker run -v $(shell pwd):/usr/src/sonar pagarme/check-sonar-quality-gate
.PHONY: sonar-check-quality-gate
