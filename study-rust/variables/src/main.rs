fn main() {
    another_function(5, 6);
    let x = five();
    println!("The value of x is: {}", x);


    let s = String::from("Hello World");
    println!("The first value of s is {}", &s[..1])
}

fn another_function(x: i32, y: i32) {
    println!("The value of x is: {}", x);
    println!("The value of y is: {}", y);
}

fn five() -> i32 {
    return 5
}

