.PHONY: pack clean

pack:
	cd src && tar -czvf ../xsmahe01.tgz parse/ test/ interpreter/ *.php *.py *.md rozsireni

clean:
	rm xsmahe01.tgz
