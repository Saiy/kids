BEGIN{

	config["method"] = 3;
	config["time"]   = 4;
	config["ip"]     = 1;

	split("ip|time|method|request_file|status|byte" , keys , "|");

}


function check(input,type,path) {
	executed = 0;
	if (type == "method") {
		executed = 1;
		if (length(input) < 8) {
			if(input ~ /\"(GET|POST|TRANCE|HEAD)/) {
				return input;
			}
		}
	}

	if (type == "time") {
		executed = 1;
		a = "[25/Nov/2013:04:52:21";
		if (substr(input,1,1) == "[") {
			preg_tmp_a = substr(input,2,1);
			preg_tmp_b = substr(input,3,1);
			if ( preg_tmp_a ~ /0|1|2|3/) {
				if (preg_tmp_b ~ /0|1|2|3|4|5|6|7|8|9/) {
					return input;
				}
			}
		}
	}

	if (type == "ip") {
		executed = 1;
		num = 0;
		if (substr(input,1,1) ~ /1|2|3|4|5|6|7|8|9/) {
			split(input , array , ".");
			for(key in array) {
				if(substr(array[key],1,1) ~ /1|2|3|4|5|6|7|8|9|0/) {
					num ++;
				}
			}
		}

		if(num == 4) {
			return input;
		}

	}

	if(executed == 0) {
		return input;
	}
}

function clean(input,type) {
	if (type == "method") {
		if(check(input,type)) {
			return substr(input,2,length(input));
		}
	}

	if (type == "time") {
		if(check(input,type)) {
			return substr(input,2,length(input));
		}
	}

	return input;

}

{

	for (type in config) {
		data[type] = "";
	}

	for (type in config) {
		if ( check($config[type],type) ) {
			data[type] == $config[type];
		} else {

			note = "从缓存里找";

			if (cache[type]) {
				split(cache[type] , array , "|");
				for (id in array) {
					tmp = check($i,type);
					if (tmp) {
						config[type] = id;
						data[type] = tmp;
					}
				}
			}
		}
	

		exits = 0;
		if (length(check(data[type],type)) < 1) {
		
			for(i =1;i<25;i++) {
				tmp = check($i,type,i);
				if (tmp) {
					data[type] = $i;
					config[type] = i;

					exits = 0;
					if (cache[type]) {
						split(cache[type] , array , "|");
						for (id in array) {
							if (id == i) {
								exits = 1;
							}
						}
					}

					if (exits == 0) {
						note = "如果缓存里没有当前的id，把当前的id加入到缓存内";
						cache[type] = cache[type]"|"i;
					}

					break;
				}
			}
		}
	}

	msg = "";

	data["status"]       = $(config["method"]+3);
	data["byte"]         = $(config["method"]+4);
	data["request_file"] = $(config["method"]+1);

	for (key in keys) {
		key = keys[key];
		if(msg == "") {
			if(data[key] == "") {
				data[key] = "parser_error";
				
			}
			msg = clean(data[key],key);
			
		}else{
			if(data[key] == "") {
				data[key] = "parser_error";
				print $0;
			}
			msg = msg"\t"clean(data[key],key);
		}
	}

	print msg;
}

END {
	
}