//<script>
(function () {
	'use strict';

	var canvas;
	var context;
	var width = 1;
	var height = 1;
	var pixelRatio = 1;
	var schools = [];
	var animationFrame = null;
	var lastTimestamp = 0;
	var spawnCountdown = 0;
	var nextSchoolId = 1;
	var mouse = { x: -10000, y: -10000 };

	var config = {
		maxSchools: 6,
		mobileMaxSchools: 4,
		initialSchools: 3,
		mobileInitialSchools: 2,
		minSchoolSize: 5,
		maxSchoolSize: 10,
		mobileMinSchoolSize: 4,
		mobileMaxSchoolSize: 8,
		maxParticles: 56,
		mobileMaxParticles: 30,
		maxSpeed: 1.25,
		minSpeed: 0.22,
		maxForce: 0.018,
		perceptionRadius: 94,
		separationDistance: 26,
		separationWeight: 1.8,
		alignmentWeight: 0.9,
		cohesionWeight: 0.7,
		wanderWeight: 0.45,
		directionWeight: 0.7,
		mouseRadius: 110,
		fadeIn: 850,
		fadeOut: 2400,
		lifeMin: 10500,
		lifeMax: 17500,
		spawnMin: 2600,
		spawnMax: 5200,
		mobileSpawnMin: 3400,
		mobileSpawnMax: 6200
	};

	function randomBetween(minimum, maximum) {
		return minimum + (Math.random() * (maximum - minimum));
	}

	function limit(value, minimum, maximum) {
		return Math.max(minimum, Math.min(maximum, value));
	}

	function vectorLength(x, y) {
		return Math.sqrt((x * x) + (y * y));
	}

	function normalise(x, y) {
		var length = vectorLength(x, y) || 1;
		return { x: x / length, y: y / length };
	}

	function limitVector(x, y, maximum) {
		var length = vectorLength(x, y);
		if (!length || length <= maximum) {
			return { x: x, y: y };
		}
		return { x: (x / length) * maximum, y: (y / length) * maximum };
	}

	function chooseEdge() {
		return Math.floor(Math.random() * 4);
	}

	function edgePoint(edge, margin) {
		if (edge === 0) {
			return { x: randomBetween(margin, width - margin), y: -margin };
		}
		if (edge === 1) {
			return { x: width + margin, y: randomBetween(margin, height - margin) };
		}
		if (edge === 2) {
			return { x: randomBetween(margin, width - margin), y: height + margin };
		}
		return { x: -margin, y: randomBetween(margin, height - margin) };
	}

	function oppositeEdge(edge) {
		return (edge + 2) % 4;
	}

	function createDestination(school, edge) {
		var margin = Math.max(40, Math.min(width, height) * 0.08);
		var point = edgePoint(edge, margin);
		var direction = normalise(point.x - school.centerX, point.y - school.centerY);
		school.destinationX = point.x;
		school.destinationY = point.y;
		school.directionX = direction.x;
		school.directionY = direction.y;
	}

	function School(edge, size) {
		var origin = edgePoint(edge, 34);
		this.id = nextSchoolId++;
		this.age = 0;
		this.life = randomBetween(config.lifeMin, config.lifeMax);
		this.edge = edge;
		this.centerX = origin.x;
		this.centerY = origin.y;
		this.wanderPhase = Math.random() * Math.PI * 2;
		this.wanderSpeed = randomBetween(0.00022, 0.00052);
		this.wanderAmount = randomBetween(0.6, 1.35);
		this.maxSpeed = randomBetween(0.85, config.maxSpeed);
		this.members = [];
		createDestination(this, oppositeEdge(edge));

		for (var index = 0; index < size; index++) {
			this.members.push(new Snowflake(this, origin));
		}
	}

	School.prototype.getOpacity = function () {
		if (this.age < config.fadeIn) {
			return this.age / config.fadeIn;
		}
		if (this.age > this.life - config.fadeOut) {
			return limit((this.life - this.age) / config.fadeOut, 0, 1);
		}
		return 1;
	};

	School.prototype.update = function (elapsed) {
		this.age += elapsed;
		this.centerX = 0;
		this.centerY = 0;

		for (var index = 0; index < this.members.length; index++) {
			this.centerX += this.members[index].x;
			this.centerY += this.members[index].y;
		}
		this.centerX /= this.members.length || 1;
		this.centerY /= this.members.length || 1;

		var distanceToDestination = vectorLength(
			this.destinationX - this.centerX,
			this.destinationY - this.centerY
		);
		if (distanceToDestination < Math.max(70, Math.min(width, height) * 0.12)) {
			createDestination(this, chooseEdge());
		}
	};

	School.prototype.isFinished = function () {
		return this.age >= this.life;
	};

	function Snowflake(school, origin) {
		var angle = Math.random() * Math.PI * 2;
		var speed = randomBetween(0.35, 0.75);
		this.school = school;
		this.x = origin.x + randomBetween(-18, 18);
		this.y = origin.y + randomBetween(-18, 18);
		this.vx = Math.cos(angle) * speed + school.directionX * 0.55;
		this.vy = Math.sin(angle) * speed + school.directionY * 0.55;
		this.size = randomBetween(4, 11);
		this.baseOpacity = randomBetween(0.26, 0.62);
		this.phase = Math.random() * Math.PI * 2;
		this.rotation = Math.random() * Math.PI * 2;
		this.rotationSpeed = randomBetween(-0.0016, 0.0016);
	}

	Snowflake.prototype.steer = function (desiredX, desiredY, weight) {
		var desired = normalise(desiredX, desiredY);
		var targetSpeed = this.school.maxSpeed;
		var forceX = (desired.x * targetSpeed) - this.vx;
		var forceY = (desired.y * targetSpeed) - this.vy;
		var force = limitVector(forceX, forceY, config.maxForce * weight);
		this.vx += force.x;
		this.vy += force.y;
	};

	Snowflake.prototype.flock = function (flock, timestamp) {
		var separationX = 0;
		var separationY = 0;
		var alignmentX = 0;
		var alignmentY = 0;
		var cohesionX = 0;
		var cohesionY = 0;
		var neighbours = 0;
		var closeNeighbours = 0;
		var perceptionSquared = config.perceptionRadius * config.perceptionRadius;

		for (var index = 0; index < flock.length; index++) {
			var other = flock[index];
			if (other === this || other.school !== this.school) {
				continue;
			}

			var dx = other.x - this.x;
			var dy = other.y - this.y;
			var squaredDistance = (dx * dx) + (dy * dy);
			if (!squaredDistance || squaredDistance > perceptionSquared) {
				continue;
			}

			var distance = Math.sqrt(squaredDistance);
			neighbours++;
			alignmentX += other.vx;
			alignmentY += other.vy;
			cohesionX += other.x;
			cohesionY += other.y;

			if (distance < config.separationDistance) {
				separationX -= dx / distance;
				separationY -= dy / distance;
				closeNeighbours++;
			}
		}

		if (closeNeighbours) {
			this.steer(
				separationX / closeNeighbours,
				separationY / closeNeighbours,
				config.separationWeight
			);
		}
		if (neighbours) {
			this.steer(
				(alignmentX / neighbours) - this.vx,
				(alignmentY / neighbours) - this.vy,
				config.alignmentWeight
			);
			this.steer(
				(cohesionX / neighbours) - this.x,
				(cohesionY / neighbours) - this.y,
				config.cohesionWeight
			);
		}

		var school = this.school;
		var wanderAngle = (timestamp * school.wanderSpeed) + this.phase + school.wanderPhase;
		this.steer(
			Math.cos(wanderAngle) * school.wanderAmount,
			Math.sin(wanderAngle) * school.wanderAmount,
			config.wanderWeight
		);
		this.steer(school.directionX, school.directionY, config.directionWeight);
		this.steer(
			school.destinationX - this.x,
			school.destinationY - this.y,
			config.directionWeight
		);

		var mouseX = this.x - mouse.x;
		var mouseY = this.y - mouse.y;
		var mouseDistanceSquared = (mouseX * mouseX) + (mouseY * mouseY);
		if (mouseDistanceSquared < config.mouseRadius * config.mouseRadius) {
			var mouseDistance = Math.sqrt(mouseDistanceSquared) || 1;
			var repulsion = (config.mouseRadius - mouseDistance) / config.mouseRadius;
			this.steer(mouseX / mouseDistance, mouseY / mouseDistance, 5 * repulsion);
		}

		var speed = vectorLength(this.vx, this.vy);
		if (speed > config.maxSpeed) {
			this.vx = (this.vx / speed) * config.maxSpeed;
			this.vy = (this.vy / speed) * config.maxSpeed;
		} else if (speed < config.minSpeed) {
			var safeSpeed = speed || 1;
			this.vx = (this.vx / safeSpeed) * config.minSpeed;
			this.vy = (this.vy / safeSpeed) * config.minSpeed;
		}
	};

	Snowflake.prototype.update = function (step) {
		this.x += this.vx * step;
		this.y += this.vy * step;
		this.rotation += this.rotationSpeed * step;
	};

	Snowflake.prototype.draw = function (schoolOpacity) {
		context.save();
		context.translate(this.x, this.y);
		/* Rotation is independent from velocity: a snowflake is not a fish. */
		context.rotate(this.rotation);
		context.strokeStyle = 'rgba(49, 92, 122, ' + (this.baseOpacity * schoolOpacity) + ')';
		context.lineWidth = 1.25;
		context.lineCap = 'round';
		context.beginPath();

		for (var arm = 0; arm < 3; arm++) {
			context.moveTo(-this.size, 0);
			context.lineTo(this.size, 0);
			context.moveTo(this.size * 0.48, 0);
			context.lineTo(this.size * 0.2, this.size * 0.25);
			context.moveTo(this.size * 0.48, 0);
			context.lineTo(this.size * 0.2, -this.size * 0.25);
			context.rotate(Math.PI / 3);
		}

		context.stroke();
		context.restore();
	};

	function activeParticleCount() {
		var count = 0;
		for (var index = 0; index < schools.length; index++) {
			count += schools[index].members.length;
		}
		return count;
	}

	function spawnSchool() {
		var isMobile = window.innerWidth <= 991;
		var minimumSize = isMobile ? config.mobileMinSchoolSize : config.minSchoolSize;
		var maximumSize = isMobile ? config.mobileMaxSchoolSize : config.maxSchoolSize;
		var particleLimit = isMobile ? config.mobileMaxParticles : config.maxParticles;
		var remaining = particleLimit - activeParticleCount();
		if (remaining < minimumSize) {
			return;
		}

		var size = Math.min(
			Math.floor(randomBetween(minimumSize, maximumSize + 1)),
			remaining
		);
		schools.push(new School(chooseEdge(), size));
	}

	function scheduleNextSchool() {
		var isMobile = window.innerWidth <= 991;
		spawnCountdown = randomBetween(
			isMobile ? config.mobileSpawnMin : config.spawnMin,
			isMobile ? config.mobileSpawnMax : config.spawnMax
		);
	}

	function startInitialSchools() {
		var isMobile = window.innerWidth <= 991;
		var initialCount = isMobile ? config.mobileInitialSchools : config.initialSchools;
		for (var index = 0; index < initialCount; index++) {
			spawnSchool();
		}
		scheduleNextSchool();
	}

	function resize() {
		var bounds = canvas.getBoundingClientRect();
		width = Math.max(1, window.innerWidth, bounds.width || 0);
		height = Math.max(1, window.innerHeight, bounds.height || 0);
		pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
		canvas.width = Math.round(width * pixelRatio);
		canvas.height = Math.round(height * pixelRatio);
		canvas.style.width = '100vw';
		canvas.style.height = '100vh';
		context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
	}

	function updateMouse(event) {
		var bounds = canvas.getBoundingClientRect();
		mouse.x = event.clientX - bounds.left;
		mouse.y = event.clientY - bounds.top;
		if (mouse.x < 0 || mouse.x > width || mouse.y < 0 || mouse.y > height) {
			mouse.x = -10000;
			mouse.y = -10000;
		}
	}

	function update(elapsed, timestamp) {
		spawnCountdown -= elapsed;
		var isMobile = window.innerWidth <= 991;
		var schoolLimit = isMobile ? config.mobileMaxSchools : config.maxSchools;
		if (spawnCountdown <= 0 && schools.length < schoolLimit) {
			spawnSchool();
			scheduleNextSchool();
		}

		var activeSchools = [];
		for (var index = 0; index < schools.length; index++) {
			var school = schools[index];
			if (school.isFinished()) {
				continue;
			}
			school.update(elapsed);
			activeSchools.push(school);
		}
		schools = activeSchools;

		var flock = [];
		for (var schoolIndex = 0; schoolIndex < schools.length; schoolIndex++) {
			flock = flock.concat(schools[schoolIndex].members);
		}
		for (var particleIndex = 0; particleIndex < flock.length; particleIndex++) {
			flock[particleIndex].flock(flock, timestamp);
		}
		return flock;
	}

	function render(timestamp) {
		if (document.hidden) {
			animationFrame = null;
			return;
		}
		if (!lastTimestamp) {
			lastTimestamp = timestamp;
		}
		var elapsed = limit(timestamp - lastTimestamp, 0, 50);
		var step = limit(elapsed / 16.67, 0.25, 2.5);
		lastTimestamp = timestamp;
		context.clearRect(0, 0, width, height);

		var flock = update(elapsed, timestamp);
		for (var index = 0; index < flock.length; index++) {
			var particle = flock[index];
			particle.update(step);
			particle.draw(particle.school.getOpacity());
		}
		animationFrame = window.requestAnimationFrame(render);
	}

	function resumeAnimation() {
		lastTimestamp = 0;
		if (!animationFrame) {
			animationFrame = window.requestAnimationFrame(render);
		}
	}

	function init() {
		if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}
		if (!document.querySelector('.ossn-layout-newsfeed, .ossn-wall-container, .user-activity, .ossn-wall-item, .sreda-registration-only, .sreda-login-only')) {
			return;
		}
		if (document.getElementById('sibcore-boids-canvas')) {
			return;
		}

		/* Put the fixed layer directly under body so no OSSN parent can clip it. */
		canvas = document.createElement('canvas');
		canvas.id = 'sibcore-boids-canvas';
		canvas.setAttribute('aria-hidden', 'true');
		document.body.insertBefore(canvas, document.body.firstChild);
		context = canvas.getContext('2d');
		if (!context) {
			canvas.remove();
			return;
		}

		resize();
		startInitialSchools();
		window.addEventListener('resize', resize, { passive: true });
		document.addEventListener('mousemove', updateMouse, { passive: true });
		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				if (animationFrame) {
					window.cancelAnimationFrame(animationFrame);
					animationFrame = null;
				}
			} else {
				resumeAnimation();
			}
		});
		window.addEventListener('blur', function () {
			mouse.x = -10000;
			mouse.y = -10000;
		});
		animationFrame = window.requestAnimationFrame(render);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
