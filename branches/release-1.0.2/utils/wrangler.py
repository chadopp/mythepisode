#! /usr/bin/env python
'''This script can grab a list of shows from epguides.com
and retrieve a list of each shows episodes and a synopsis
for each episode. Written by pcable'''

import urllib, string, sys, re, os, time, getopt

class Shows(object):
	'''Class to facilitate downloading a list of TV shows'''
	
	# init method, called when an instance is created
	def __init__(self, url1=None, url2=None, path=None, file=None):
		if url1 == None: self.url1 = 'http://epguides.com/menu/year.shtml'
		else: self.url1 = url1
		if url2 == None: self.url2 = 'http://epguides.com/menu/current.shtml'
		else: self.url2 = url2
		if path == None: self.path = '/tmp/wrangler/'
		else: self.path = path
		if file == None: self.file = "shows.txt"
		else: self.file = file
	
	# grab the show data from epguides.com and parse it
	# write it to a text file when we are done.
	def getShows(self):
		# grab the data
		if debug == 1:
			print 'Grabbing ' + self.url1
		f1 = urllib.urlopen(self.url1)
		result1 = f1.read().splitlines()
		f1.close()
		if debug == 1:
			print 'Grabbing ' + self.url2
		f2 = urllib.urlopen(self.url2)
		result2 = f2.read().splitlines()
		f2.close()
	
		# clean it up and sort it alphabetically
		sortable = []
		stripped = {}
		output = ''
		for line in result1:
			if line.find("a href") != -1:
				if line.find("epguides") == -1 and line.find("genre") == -1 and line.find("<td><a href") == -1  and line.find("menu") == -1  and line.find("http") == -1:
					line = line.replace('<a href="../','',1)
					line = line.replace('<li>','',1)
					line = line.replace('<b>','',1)
					line = line.replace('</a>','',1)
					line = line.replace('</li>','',1)
					line = line.replace('/">','\t',1)
					line = line.replace('</a><br>','',1)
					line = line.replace('</a> (US)<br>','',1)
					line = line.replace(' (US)','',1)
					line = line.replace('</a> (anim)<br>',' (anim)',1)
					line = line.replace(' [anim]','',1)
					line = line.replace('</b>','',1)
                                        #line = line.split("\'") [0]
					stripped[line.lower()] = line
					sortable.append(line.lower())
		
		if debug == 1:
			print 'Finished stripping page data.'
		
		# mark whether a show appears on the current page or not
		for key in stripped.iterkeys():
			found = 0
			searchkey = key.split('\t')[0]
			for line in result2:
				if line.lower().find(searchkey) > -1:
					found = 1
			if found == 1:
				stripped[key] += '\t1'
			else:
				stripped[key] += '\t0'
	
		sortable.sort()
		for x in sortable:
			output = output + '\n' + stripped[x]
		
		if debug == 1:
			print 'Finished marking and sorting data'
		
		# make sure our output directory exists
		if not os.path.exists(self.path):
			os.makedirs(self.path)
			if debug == 1:
				print 'Creating directory ' + self.path
		
		# write the file
		try:
			f = open(self.path + self.file,mode='w')
			f.write(output.strip())
			f.close()
			if debug == 1:
				print 'Finished writing ' + self.path + self.file
		except:
			print 'Unable to open ' + self.path + self.file + ' for writing.'
		
		if debug == 1:
			print 'Exiting getEpisodes()'

class Episodes(object):
	'''Class to grab a list of episodes for a given TV series'''
	
	# Init method, called when an instance is created
	def __init__(self, series, url=None, maxqueries=None, path=None):
		self.series = series
		if url == None: self.url = 'http://epguides.com/'
		else: self.url = url
		if maxqueries == None: self.maxqueries = 3
		else: self.maxqueries = int(maxqueries)
		if path == None: self.path = '/tmp/wrangler/'
		else: self.path = path
		# This seems to remind python that maxqueries is an integer not a string
		type(maxqueries)
	
	# grab the episode data from epguides.com, parse it, save it
	# then send <maxqueries> jobs to getSynopsis for processing
	def getEpisodes(self):		
		# Get the raw html
		if debug == 1:
			print 'Grabbing the data from ' + self.url
		u = urllib.urlopen(self.url + self.series)
		result = u.read().splitlines()
		u.close()
		
		# find all lines between the first <pre> and </pre>
		# and use them to populate a new list
		counter = 0
		stripped = []
		parsed = ''
		for line in result:
			if line.find("<pre>") > -1:
				counter = 1
			elif line.find("</pre>") > -1:
				break
			elif counter == 1:
				stripped.append(line)
	          	
		# Clean up and reformat into a tab-delimited
		# string. Episode #, Date, Link, Episode Name
		
		# This is a regular expression (RE) to match the date string
		datere2 = re.compile(r'.\d\s\w\w\w\s\d\d')
		datere = re.compile(r'\d{2}\/\w{3}\/\d{2}')
		#datere2 = re.compile(r'\d{2} \w{3} \d{2}')
		# This RE matches any links
		linkre = re.compile(r'<a.*?</??a>??')
		# This RE matches a URL from the previous RE
		urlre = re.compile(r'\'.*\'')
		urlre2 = re.compile(r'\".*\"')
		# This RE matches the Episode Name from linkre
		namere = re.compile(r'>.*<')
		# This RE finds epguide urls
		guidere = re.compile(r'guide.shtml')
		
		urls = []
		episodeNumber = 1
		
		for line in stripped:
			# Some shows have <li> tags within the <pre></pre> block
			# This should parse them
			if line.find("<li>") == 0:
				line = str(episodeNumber) + '\t' + line[22:32] + '\t' + line[34:]
				episodeNumber += 1
			# This block should parse the remaining shows
			elif len(line) > 60 and line.find("_____") == -1:
                                # This is messed up but I can't figure a better way.  Please fix.
				datetest = datere.findall(line)
				datetest2 = datere2.findall(line)
                                if len(datetest) > 0:
                                        date = datetest
                                if len(datetest2) > 0:
                                        date = datetest2
				if len(date) > 0:
					date = date[0]
				else:
                                        date = 'UNK'
                                # This is messed up but I can't figure a better way.  Please fix.
				linktest = linkre.findall(line)
				#linktest2 = linkre2.findall(line)
                                if len(linktest) > 0:
                                        link = linktest
                                #if len(linktest2) > 0:
                                #        link = linktest2
				if len(link) > 0:
					link = link[0]
					# Parse the link, splitting the url and the episode name
					# first remove all occurances of "visit"
					link = link.replace('"visit"','',1)
					link = link.replace('target="_blank" ','',1)
					#link = link.replace('<a href=\'','',1)
					#link = link.replace('\'>','',1)
                                        #link = link.split("\'>") [0]
                                        # This is messed up but I can't figure a better way.  Please fix.
					urltest = urlre.findall(link)
					urltest2 = urlre2.findall(link)
                                        if len(urltest) > 0:
                                                url = urltest
                                        if len(urltest2) > 0:
                                                url = urltest2
					if len(url) > 0:
						url = url[0]
						# strip the ""
						url = url[1:-1]
						# Check if this is an epguide link
						# If so, prepend http://epguides.com
						if guidere.match(url):
							url = base_url + sys.argv[1] + '/' + url
					else:
						url = 'UNK'
                                        url = url.split("\'>") [0]
					urls.append(url)
					name = namere.findall(link)
					if len(name) > 0:
						name = name[0]
						# strip the ><
						name = name[1:-1]
					else:
						name = 'UNK'
				# Couldn't find a link, we need to find the episode name elsewhere
				else:
					name = ''
				line = str(episodeNumber) + '\t' + date + '\t' + name + '\t' + url
				episodeNumber += 1
			else:
				line = ""
			if len(line) > 1:
				parsed = parsed + '\n' + line
		if debug == 1:
			print 'Finished parsing. Saving data to ' + self.path + self.series
		if not os.path.exists(self.path):
			os.makedirs(self.path)
		f = open(self.path + self.series,mode='w')
		f.write(parsed.strip())
		f.close()
		
		commands = []
		# initialize the list of tuples
		# to avoid out of range exceptions
                self.maxqueries = episodeNumber
                self.maxqueries -= 1
                if urls == []:
   			self.maxqueries = 0
                #print self.maxqueries 
		for i in xrange(self.maxqueries):
			if debug == 1:
				commands.append(('--synopsis', '-s', series, '-p', self.path, '-q', str(i), '-d'))
			else:
				commands.append(('--synopsis', '-s', series, '-p', self.path, '-q', str(i)))
			
		query = 0
		for url in urls:
		       	commands[query] += (url,)
		       	query += 1
		       	if query == self.maxqueries:
		       		 query = 0
		# fork to fire off <maxqueries> processes
		for args in commands:
                        #time.sleep(1)
			pid=os.fork()
			if not pid:
				if debug == 1:
				        print 'Launching getSynopsis process with args: ' + str(args)
				os.execvp(mypath,(mypath,)+args)
				
		# Wait for all processes to complete
		loop = 1
		timer = 0
		while loop == 1:
			loop = 0
			for x in xrange(self.maxqueries):
				if os.path.exists(self.path + '.' + self.series + str(x)):
					loop = 1
			time.sleep(.1)			
			timer += 1
			# I think we've waited long enough, something must be wrong
			if timer >= 1000:
				if debug == 1:
					print 'Timed out waiting for synopsis downloads'
				loop = 0
				for x in xrange(self.maxqueries):
					if os.path.exists(self.path + '.' + self.series + str(x)):
						os.remove(self.path + '.' + self.series + str(x))
		if debug == 1:
			print 'Exiting getEpisodes()'

class Synopsis(object):
	'''Class to grab the synopsis data for each episode from tv.com'''
	
	# Init method, called when an instance is created
	def __init__(self, series, query, path=None, urls=None):
		self.series = series
		self.query = query
		if urls == None:
			print 'Error. Need some urls!'
		else:
			self.urls = urls
		if path == None: self.path = '/tmp/wrangler/'
		else: self.path = path
		self.biglockfile = self.path + '.' + self.series + self.query
		self.lockfile = self.path + '.' + self.series
		if debug == 1:
			print 'biglockfile = ' + self.biglockfile
			print 'lockfile = ' + self.lockfile
	
	# Grab the data from the supplied url(s), parse it and write it
	# out to the existing file created by getEpisodes() 
	def getSynopsis(self):
		if debug == 1:
			print 'Writing synopsis data to ' + self.path + self.series
		outfile = self.path + self.series
		# open our big 'o' lockfile so we can
		# let getEpisodes know when we are done!
		biglock = open(self.biglockfile,mode='w')
		
		counter = 0
		episode = 1
		
		for url in self.urls:
			if debug == 1: print url
			synopsis = ''
			counter = 0
		
			if url.find('tvrage.com') > -1:
				# We know how to grab the synopsis from tv.com
				u = urllib.urlopen(url)
				synop = u.read().splitlines()
				u.close()
		
				for line in synop:
					#if line.find('<p class="m-0">') > -1:
					#if line.find('</script><br>') > -1:
					if line.find('</script><br>') == 0:
						counter = 1
					#elif line.find('<br>') > -1:
						#if counter == 1:
						#	break
					#elif line.find('<div') > -1:
					#	counter -= 1
					#elif line.find('</div') > -1:
					#	counter += 1
					#elif counter == 1:
						synopsis += line
				synopsis = synopsis.replace('</script><br>','')
				synopsis = synopsis.replace('&nbsp;</td></tr><tr>','')
                                synopsis = synopsis.split("<b") [0]
				synopsis = synopsis.strip()
                                if synopsis == '':
                                        synopsis = 'No data'
                                #print synopsis
			else:
				synopsis = 'No data'
		
			loop = 1
			
			while loop == 1:
				# Check if another process holds the lockfile
				if not os.path.exists(self.lockfile):
					# create the lock file
					lock = open(self.lockfile,mode='w')
					if debug == 1:
						print 'Got lockfile, writing data to ' + outfile
					# Read the data in
					try:
						fi = open(outfile,mode='r')
						episodes = fi.read().splitlines()
						fi.close()
					except:
						print 'Unable to open file for reading'
			
					output = ''
			
					for line in episodes:
						if line.find(url) > -1:
							line = line + '\t' + synopsis
							if debug == 1:
								print 'Matched synopsis to episode'
						output = output + '\n' + line
		
					try:
						fo = open(outfile,mode='w')
						fo.write(output.strip())
						fo.close()
					except:
						print 'Unable to open file for writing'
					
					# close and delete the lock file
					lock.close()
					os.remove(self.lockfile)
				
					episode = episode + 1
					loop = 0
				else:
					time.sleep(.1)
					
		biglock.close()
		os.remove(self.biglockfile)
		if debug == 1:
			print 'Exiting getSynopsis()'

def usage():
	print '''
Usage: wrangler.py [--shows] [--episodes] [--synopsis] [--all] [-p wrangler_path] [-s series_name] [-q query_num] <urls>
	
	(MODES)
	
	--shows: Creates a file in <wrangler_path> called shows.txt with a list of known shows
	
	--episodes: Creates a file in <wrangler_path>/shows called <series> with a list of known episodes
	
	--synopsis: Appends a synopsis to each episode in a previously created episode file
	
	--all: Attempts to download an episode/synopsis list for every known show
		(Be advised, this will probably take a very long time!)
		
	(OPTIONS)
		
	-p <wrangler_path>: Sets the location for files to be stored. Defaults to /tmp/wrangler
	
	-s <series>: Sets which TV series to find episodes for
	
	-q <query>: Sets the query number for running multiple simultaneous queries
	
	-m <max_queries>: Sets the maximum number of simultaneous queries
	
	<urls>: A list of urls from which to retrieve synopsis data'''

# Print a usage statement if we are called with no arguments
if len(sys.argv) < 2:
	usage()
	sys.exit(1)	

try:
	(options,args) = getopt.getopt(sys.argv[1:],'dp:s:q:m:',['shows','episodes','synopsis','all'])
except:
	usage()

# Remember our path, we'll need it to call getSynopsis()
mypath = sys.argv[0]

# Determine what mode we have been called in
mode = None
path = None
series = None
query = None
maxqueries = 20
debug = 0
try:
	for (option,parameter) in options:
		if option[:2] == '--':
			if mode == None:
				mode = option[2:]
			else:
				print 'Only one mode may be selected!'
				usage()
				sys.exit(1)
	
		elif option == '-p':
			path = parameter
		elif option == '-s':
			series = parameter
		elif option == '-q':
			query = parameter
		elif option == '-d':
			debug = 1
		elif option == '-m':
			maxqueries = parameter
except:
	usage()

if debug == 1:
	print sys.argv[0]
	print options + args

# Fill in the default values for any undefined parameters
if path == None: path = '/tmp/wrangler/'
if mode == 'shows':
	s = Shows(path=path)
	s.getShows()
elif mode == 'episodes':
	if series == None:
		print 'You need to tell me what series you want to see!'
		usage()
		sys.exit(1)
	else:
		e = Episodes(series,path=path,maxqueries=maxqueries)
		e.getEpisodes()
elif mode == 'synopsis':
	if series == None or query == None or len(args) < 1:
		print 'Malformed synopsis query for series!'
                print series
                print query
		usage()
		sys.exit(1)
	else:
		y = Synopsis(series,query,path=path,urls=args)
		y.getSynopsis()
		
